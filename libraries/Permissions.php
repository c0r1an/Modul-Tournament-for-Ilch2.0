<?php

namespace Modules\Tournament\Libraries;

use Modules\User\Models\User;

class Permissions
{
    public const ADMIN = 'tournament_admin';
    public const MANAGE = 'tournament_manage';
    public const DISPUTE = 'tournament_dispute';
    public const TEAM_MANAGE = 'tournament_team_manage';
    public const REPORT = 'tournament_report';

    public static function canAdmin(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->isAdmin() || $user->hasAccess('module_tournament');
    }

    public static function canManageTournament(?User $user): bool
    {
        return self::canAdmin($user);
    }

    public static function canResolveDispute(?User $user): bool
    {
        return self::canAdmin($user);
    }
}
