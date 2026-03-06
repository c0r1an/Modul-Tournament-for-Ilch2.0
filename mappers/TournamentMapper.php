<?php

namespace Modules\Tournament\Mappers;

use Ilch\Mapper;

class TournamentMapper extends Mapper
{
    public function getAll(array $where = [], array $order = ['start_at' => 'DESC']): array
    {
        return $this->db()->select('*')
            ->from('tournament_tournaments')
            ->where($where)
            ->order($order)
            ->execute()
            ->fetchRows() ?: [];
    }

    public function getById(int $id): ?array
    {
        $row = $this->db()->select('*')
            ->from('tournament_tournaments')
            ->where(['id' => $id])
            ->execute()
            ->fetchAssoc();

        return $row ?: null;
    }

    public function save(array $data, ?int $id = null): int
    {
        if ($id) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->db()->update('tournament_tournaments')
                ->values($data)
                ->where(['id' => $id])
                ->execute();
            return $id;
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return (int) $this->db()->insert('tournament_tournaments')->values($data)->execute();
    }

    public function setStatus(int $id, string $status): void
    {
        $this->db()->update('tournament_tournaments')
            ->values(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')])
            ->where(['id' => $id])
            ->execute();
    }

    public function delete(int $id): bool
    {
        return (bool)$this->db()->delete('tournament_tournaments')
            ->where(['id' => $id])
            ->execute();
    }

    public function getRunningForBox(int $limit = 5): array
    {
        $limit = max(1, $limit);

        return $this->db()->select('*')
            ->from('tournament_tournaments')
            ->where(['status' => 'running'])
            ->order(['start_at' => 'ASC'])
            ->limit($limit)
            ->execute()
            ->fetchRows() ?: [];
    }
}
