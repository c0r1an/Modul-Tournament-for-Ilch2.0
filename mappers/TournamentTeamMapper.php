<?php

namespace Modules\Tournament\Mappers;

use Ilch\Mapper;

class TournamentTeamMapper extends Mapper
{
    public function getByTournamentId(int $tournamentId): array
    {
        return $this->db()->select(['tt.*', 'team_name' => 't.name', 'tag' => 't.tag', 'logo' => 't.logo'])
            ->from(['tt' => 'tournament_tournament_teams'])
            ->join(['t' => 'tournament_teams'], 'tt.team_id = t.id', 'INNER')
            ->where(['tt.tournament_id' => $tournamentId])
            ->order(['tt.id' => 'ASC'])
            ->execute()
            ->fetchRows() ?: [];
    }

    public function getAcceptedByTournamentId(int $tournamentId): array
    {
        return $this->getByTournamentAndStatuses($tournamentId, ['accepted', 'checked_in']);
    }

    public function getCheckedInByTournamentId(int $tournamentId): array
    {
        return $this->getByTournamentAndStatuses($tournamentId, ['checked_in']);
    }

    public function getByTournamentAndTeam(int $tournamentId, int $teamId): ?array
    {
        $row = $this->db()->select('*')
            ->from('tournament_tournament_teams')
            ->where(['tournament_id' => $tournamentId, 'team_id' => $teamId])
            ->execute()
            ->fetchAssoc();

        return $row ?: null;
    }

    public function markCheckedIn(int $tournamentId, int $teamId): void
    {
        $this->db()->update('tournament_tournament_teams')
            ->values(['status' => 'checked_in'])
            ->where(['tournament_id' => $tournamentId, 'team_id' => $teamId, 'status' => 'accepted'])
            ->execute();
    }

    public function isRegistered(int $tournamentId, int $teamId): bool
    {
        return (bool) $this->db()->select('COUNT(*)')->from('tournament_tournament_teams')->where(['tournament_id' => $tournamentId, 'team_id' => $teamId])->execute()->fetchCell();
    }

    public function getAcceptedCount(int $tournamentId): int
    {
        $accepted = (int) $this->db()->select('COUNT(*)')->from('tournament_tournament_teams')->where(['tournament_id' => $tournamentId, 'status' => 'accepted'])->execute()->fetchCell();
        $checkedIn = (int) $this->db()->select('COUNT(*)')->from('tournament_tournament_teams')->where(['tournament_id' => $tournamentId, 'status' => 'checked_in'])->execute()->fetchCell();

        return $accepted + $checkedIn;
    }

    public function registerAccepted(int $tournamentId, int $teamId): int
    {
        return (int) $this->db()->insert('tournament_tournament_teams')->values([
            'tournament_id' => $tournamentId,
            'team_id' => $teamId,
            'status' => 'accepted',
            'registered_at' => date('Y-m-d H:i:s'),
        ])->execute();
    }

    public function updateSeed(int $id, ?int $seed): void
    {
        $this->db()->update('tournament_tournament_teams')->values(['seed' => $seed])->where(['id' => $id])->execute();
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->db()->update('tournament_tournament_teams')->values(['status' => $status])->where(['id' => $id])->execute();
    }

    /**
     * @param string[] $statuses
     */
    private function getByTournamentAndStatuses(int $tournamentId, array $statuses): array
    {
        $rows = [];
        foreach ($statuses as $status) {
            $part = $this->db()->select(['tt.*', 'team_name' => 't.name', 'tag' => 't.tag', 'logo' => 't.logo'])
                ->from(['tt' => 'tournament_tournament_teams'])
                ->join(['t' => 'tournament_teams'], 'tt.team_id = t.id', 'INNER')
                ->where(['tt.tournament_id' => $tournamentId, 'tt.status' => $status])
                ->execute()
                ->fetchRows() ?: [];
            foreach ($part as $row) {
                $rows[] = $row;
            }
        }

        usort($rows, static function (array $a, array $b): int {
            $seedA = (int)($a['seed'] ?? 0);
            $seedB = (int)($b['seed'] ?? 0);
            if ($seedA === 0 && $seedB > 0) {
                return 1;
            }
            if ($seedB === 0 && $seedA > 0) {
                return -1;
            }
            if ($seedA !== $seedB) {
                return $seedA <=> $seedB;
            }

            return ((int)$a['id']) <=> ((int)$b['id']);
        });

        return $rows;
    }
}
