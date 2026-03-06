<?php

namespace Modules\Tournament\Libraries;

use Modules\Tournament\Mappers\MatchMapper;

class Bracket
{
    /**
     * @param int $tournamentId
     * @param int[] $teamIds
     * @return bool
     */
    public function generate(int $tournamentId, array $teamIds): bool
    {
        $teamIds = array_values(array_filter($teamIds));
        $teamCount = count($teamIds);

        if (!Status::isPowerOfTwo($teamCount)) {
            return false;
        }

        shuffle($teamIds);

        $mapper = new MatchMapper();
        $mapper->deleteByTournamentId($tournamentId);

        $roundCount = (int) log($teamCount, 2);
        $matchesPerRound = $teamCount / 2;

        $matrix = [];
        for ($round = 1; $round <= $roundCount; $round++) {
            $matrix[$round] = [];
            for ($matchNo = 1; $matchNo <= $matchesPerRound; $matchNo++) {
                $matchId = $mapper->create([
                    'tournament_id' => $tournamentId,
                    'round' => $round,
                    'match_no' => $matchNo,
                    'team1_id' => null,
                    'team2_id' => null,
                    'winner_team_id' => null,
                    'score1' => null,
                    'score2' => null,
                    'best_of' => 1,
                    'map' => null,
                    'scheduled_at' => null,
                    'status' => Status::MATCH_PENDING,
                    'next_match_id' => null,
                    'next_match_slot' => null,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $matrix[$round][$matchNo] = $matchId;
            }
            $matchesPerRound = (int) ($matchesPerRound / 2);
        }

        for ($round = 1; $round < $roundCount; $round++) {
            foreach ($matrix[$round] as $matchNo => $matchId) {
                $nextMatchNo = (int) ceil($matchNo / 2);
                $slot = ($matchNo % 2 === 1) ? 'team1' : 'team2';
                $mapper->update($matchId, [
                    'next_match_id' => $matrix[$round + 1][$nextMatchNo],
                    'next_match_slot' => $slot,
                ]);
            }
        }

        $teamIndex = 0;
        foreach ($matrix[1] as $matchId) {
            $team1 = $teamIds[$teamIndex++] ?? null;
            $team2 = $teamIds[$teamIndex++] ?? null;
            $mapper->update($matchId, [
                'team1_id' => $team1,
                'team2_id' => $team2,
                'status' => ($team1 && $team2) ? Status::MATCH_READY : Status::MATCH_PENDING,
            ]);
        }

        return true;
    }

    public function propagateWinner(int $matchId, int $winnerTeamId): void
    {
        $mapper = new MatchMapper();
        $match = $mapper->getById($matchId);
        if (!$match || empty($match['next_match_id']) || empty($match['next_match_slot'])) {
            return;
        }

        $slotField = $match['next_match_slot'] === 'team1' ? 'team1_id' : 'team2_id';
        $mapper->update((int)$match['next_match_id'], [$slotField => $winnerTeamId]);

        $next = $mapper->getById((int)$match['next_match_id']);
        if (!$next) {
            return;
        }

        if (!empty($next['team1_id']) && !empty($next['team2_id'])) {
            $mapper->update((int)$next['id'], [
                'status' => !empty($next['scheduled_at']) ? Status::MATCH_SCHEDULED : Status::MATCH_READY,
            ]);
        }
    }
}
