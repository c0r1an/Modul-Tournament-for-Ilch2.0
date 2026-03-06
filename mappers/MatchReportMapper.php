<?php

namespace Modules\Tournament\Mappers;

use Ilch\Mapper;

class MatchReportMapper extends Mapper
{
    public function create(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return (int) $this->db()->insert('tournament_match_reports')->values($data)->execute();
    }

    public function getByMatchId(int $matchId): array
    {
        return $this->db()->select('*')->from('tournament_match_reports')->where(['match_id' => $matchId])->order(['id' => 'DESC'])->execute()->fetchRows() ?: [];
    }

    public function getLatestByMatchId(int $matchId): ?array
    {
        $row = $this->db()->select('*')->from('tournament_match_reports')->where(['match_id' => $matchId])->order(['id' => 'DESC'])->limit(1)->execute()->fetchAssoc();
        return $row ?: null;
    }
}
