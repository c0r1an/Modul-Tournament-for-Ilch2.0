<?php

namespace Modules\Tournament\Libraries;

class Status
{
    public const TOURNAMENT_DRAFT = 'draft';
    public const TOURNAMENT_REGISTRATION_OPEN = 'registration_open';
    public const TOURNAMENT_REGISTRATION_CLOSED = 'registration_closed';
    public const TOURNAMENT_RUNNING = 'running';
    public const TOURNAMENT_FINISHED = 'finished';
    public const TOURNAMENT_ARCHIVED = 'archived';

    public const MATCH_PENDING = 'pending';
    public const MATCH_SCHEDULED = 'scheduled';
    public const MATCH_READY = 'ready';
    public const MATCH_REPORTED = 'reported';
    public const MATCH_CONFIRMED = 'confirmed';
    public const MATCH_DISPUTE = 'dispute';
    public const MATCH_DONE = 'done';

    public const DISPUTE_OPEN = 'open';
    public const DISPUTE_RESOLVED = 'resolved';
    public const DISPUTE_REJECTED = 'rejected';

    public static function isPowerOfTwo(int $n): bool
    {
        return $n > 1 && ($n & ($n - 1)) === 0;
    }
}
