<?php

namespace Modules\Tournament\Mappers;

use Ilch\Mapper;

class MatchMapper extends Mapper
{
    public function getById(int $id): ?array
    {
        $row = $this->db()->select('*')->from('tournament_matches')->where(['id' => $id])->execute()->fetchAssoc();
        return $row ?: null;
    }

    public function getByTournamentId(int $tournamentId): array
    {
        return $this->db()->select([
                'm.*',
                'team1_name' => 't1.name',
                'team2_name' => 't2.name',
                'team1_tag' => 't1.tag',
                'team2_tag' => 't2.tag',
                'team1_logo' => 't1.logo',
                'team2_logo' => 't2.logo',
            ])
            ->from(['m' => 'tournament_matches'])
            ->join(['t1' => 'tournament_teams'], 'm.team1_id = t1.id', 'LEFT')
            ->join(['t2' => 'tournament_teams'], 'm.team2_id = t2.id', 'LEFT')
            ->where(['m.tournament_id' => $tournamentId])
            ->order(['m.round' => 'ASC', 'm.match_no' => 'ASC'])
            ->execute()
            ->fetchRows() ?: [];
    }

    public function getGroupedByRound(int $tournamentId): array
    {
        $matches = $this->getByTournamentId($tournamentId);
        $rounds = [];
        foreach ($matches as $match) {
            $rounds[(int)$match['round']][] = $match;
        }
        ksort($rounds);
        return $rounds;
    }

    public function create(array $data): int
    {
        return (int) $this->db()->insert('tournament_matches')->values($data)->execute();
    }

    public function update(int $id, array $data): void
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db()->update('tournament_matches')->values($data)->where(['id' => $id])->execute();
    }

    public function deleteByTournamentId(int $tournamentId): void
    {
        $this->db()->delete('tournament_matches')->where(['tournament_id' => $tournamentId])->execute();
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getUpcomingForBox(int $limit = 5): array
    {
        $limit = max(1, $limit);
                $sql = 'SELECT 
                    m.id,
                    m.tournament_id,
                    m.round,
                    m.match_no,
                    (SELECT MAX(m2.round) FROM [prefix]_tournament_matches m2 WHERE m2.tournament_id = m.tournament_id) AS max_round,
                    m.scheduled_at,
                    m.status,
                    t.title AS tournament_title,
                    t.game AS tournament_game,
                    t1.tag AS team1_tag,
                    t2.tag AS team2_tag,
                    t1.logo AS team1_logo,
                    t2.logo AS team2_logo
                FROM [prefix]_tournament_matches m
                INNER JOIN [prefix]_tournament_tournaments t ON t.id = m.tournament_id
                LEFT JOIN [prefix]_tournament_teams t1 ON t1.id = m.team1_id
                LEFT JOIN [prefix]_tournament_teams t2 ON t2.id = m.team2_id
                WHERE m.status IN ("scheduled", "ready")
                  AND t.status IN ("running", "registration_closed")
                ORDER BY (m.scheduled_at IS NULL) ASC, m.scheduled_at ASC, m.round ASC, m.match_no ASC
                LIMIT ' . (int)$limit;

        return $this->db()->queryArray($sql) ?: [];
    }
}
