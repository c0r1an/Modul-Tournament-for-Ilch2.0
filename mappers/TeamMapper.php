<?php

namespace Modules\Tournament\Mappers;

use Ilch\Mapper;

class TeamMapper extends Mapper
{
    public function getAll(array $order = ['id' => 'DESC']): array
    {
        return $this->db()->select('*')
            ->from('tournament_teams')
            ->order($order)
            ->execute()
            ->fetchRows() ?: [];
    }

    public function getById(int $id): ?array
    {
        $row = $this->db()->select('*')->from('tournament_teams')->where(['id' => $id])->execute()->fetchAssoc();
        return $row ?: null;
    }

    public function getByCaptain(int $captainUserId): array
    {
        return $this->db()->select('*')->from('tournament_teams')->where(['captain_user_id' => $captainUserId])->order(['id' => 'DESC'])->execute()->fetchRows() ?: [];
    }

    public function save(array $data, ?int $id = null): int
    {
        if ($id) {
            $this->db()->update('tournament_teams')->values($data)->where(['id' => $id])->execute();
            return $id;
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return (int) $this->db()->insert('tournament_teams')->values($data)->execute();
    }

    public function isCaptain(int $teamId, int $userId): bool
    {
        return (bool) $this->db()->select('COUNT(*)')->from('tournament_teams')->where(['id' => $teamId, 'captain_user_id' => $userId])->execute()->fetchCell();
    }

    public function delete(int $id): bool
    {
        return (bool) $this->db()->delete('tournament_teams')
            ->where(['id' => $id])
            ->execute();
    }
}
