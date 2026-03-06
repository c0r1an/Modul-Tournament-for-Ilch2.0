<?php

namespace Modules\Tournament\Mappers;

use Ilch\Mapper;

class AuditMapper extends Mapper
{
    public function log(string $entity, int $entityId, string $action, array $data, int $userId): int
    {
        return (int) $this->db()->insert('tournament_audit_log')->values([
            'entity' => $entity,
            'entity_id' => $entityId,
            'action' => $action,
            'data_json' => json_encode($data),
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
        ])->execute();
    }
}
