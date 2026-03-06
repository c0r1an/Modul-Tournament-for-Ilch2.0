<?php

namespace Modules\Tournament\Mappers;

use Ilch\Mapper;

class TeamMemberMapper extends Mapper
{
    public function getById(int $id): ?array
    {
        $row = $this->db()->select('*')
            ->from('tournament_team_members')
            ->where(['id' => $id])
            ->execute()
            ->fetchAssoc();

        return $row ?: null;
    }

    public function getByTeamId(int $teamId): array
    {
        return $this->db()->select('*')->from('tournament_team_members')->where(['team_id' => $teamId])->order(['id' => 'ASC'])->execute()->fetchRows() ?: [];
    }

    public function countPlayers(int $teamId): int
    {
        $count = 0;
        foreach ($this->getByTeamId($teamId) as $member) {
            if (in_array((string)$member['role'], ['captain', 'member'], true)) {
                $count++;
            }
        }

        return $count;
    }

    public function add(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return (int) $this->db()->insert('tournament_team_members')->values($data)->execute();
    }

    public function getByTeamAndUser(int $teamId, int $userId): ?array
    {
        $row = $this->db()->select('*')
            ->from('tournament_team_members')
            ->where(['team_id' => $teamId, 'user_id' => $userId])
            ->execute()
            ->fetchAssoc();

        return $row ?: null;
    }

    public function updateRole(int $id, string $role): void
    {
        $this->db()->update('tournament_team_members')
            ->values(['role' => $role])
            ->where(['id' => $id])
            ->execute();
    }

    public function remove(int $id): void
    {
        $this->db()->delete('tournament_team_members')->where(['id' => $id])->execute();
    }

    public function isUserInTeam(int $teamId, int $userId): bool
    {
        return (bool) $this->db()->select('COUNT(*)')->from('tournament_team_members')->where(['team_id' => $teamId, 'user_id' => $userId])->execute()->fetchCell();
    }
}
