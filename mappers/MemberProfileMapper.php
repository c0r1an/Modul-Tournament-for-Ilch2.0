<?php

namespace Modules\Tournament\Mappers;

use Ilch\Mapper;

class MemberProfileMapper extends Mapper
{
    public function getByTeamMemberId(int $teamMemberId): ?array
    {
        $row = $this->db()->select('*')
            ->from('tournament_member_profiles')
            ->where(['team_member_id' => $teamMemberId])
            ->execute()
            ->fetchAssoc();

        return $row ?: null;
    }

    public function saveForTeamMember(int $teamMemberId, array $data): void
    {
        $existing = $this->getByTeamMemberId($teamMemberId);
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($existing) {
            $this->db()->update('tournament_member_profiles')
                ->values($data)
                ->where(['team_member_id' => $teamMemberId])
                ->execute();
            return;
        }

        $data['team_member_id'] = $teamMemberId;
        $this->db()->insert('tournament_member_profiles')
            ->values($data)
            ->execute();
    }
}
