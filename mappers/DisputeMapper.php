<?php

namespace Modules\Tournament\Mappers;

use Ilch\Mapper;

class DisputeMapper extends Mapper
{
    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return (int) $this->db()->insert('tournament_match_disputes')->values($data)->execute();
    }

    public function getOpen(): array
    {
        return $this->getAll('open');
    }

    public function getAll(string $status = ''): array
    {
        $query = $this->db()->select([
            'd.*',
            'm.tournament_id',
            'm.round',
            'm.match_no',
            'm.team1_id',
            'm.team2_id'
        ])
            ->from(['d' => 'tournament_match_disputes'])
            ->join(['m' => 'tournament_matches'], 'd.match_id = m.id', 'INNER')
            ->order(['d.id' => 'DESC']);

        if ($status !== '') {
            $query->where(['d.status' => $status]);
        }

        return $query->execute()->fetchRows() ?: [];
    }

    public function getById(int $id): ?array
    {
        $row = $this->db()->select('*')->from('tournament_match_disputes')->where(['id' => $id])->execute()->fetchAssoc();
        return $row ?: null;
    }

    public function getByMatchId(int $matchId): array
    {
        return $this->db()->select('*')->from('tournament_match_disputes')->where(['match_id' => $matchId])->order(['id' => 'DESC'])->execute()->fetchRows() ?: [];
    }

    public function resolve(int $id, string $status, int $userId, string $note): void
    {
        $this->db()->update('tournament_match_disputes')
            ->values([
                'status' => $status,
                'resolved_by_user_id' => $userId,
                'resolution_note' => $note,
                'resolved_at' => date('Y-m-d H:i:s'),
            ])
            ->where(['id' => $id])
            ->execute();
    }

    public function updateById(int $id, array $data): void
    {
        if (empty($data)) {
            return;
        }

        $this->db()->update('tournament_match_disputes')
            ->values($data)
            ->where(['id' => $id])
            ->execute();
    }

    public function delete(int $id): bool
    {
        return (bool)$this->db()->delete('tournament_match_disputes')
            ->where(['id' => $id])
            ->execute();
    }
}
