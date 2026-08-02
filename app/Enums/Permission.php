<?php

namespace App\Enums;

enum Permission: string
{
    case UsersViewAny = 'users.viewAny';
    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDelete = 'users.delete';

    case ActivityLogsViewAny = 'activity_logs.viewAny';
    case ActivityLogsView = 'activity_logs.view';

    case AdminsViewAny = 'admins.viewAny';
    case AdminsView = 'admins.view';
    case AdminsCreate = 'admins.create';
    case AdminsUpdate = 'admins.update';
    case AdminsDelete = 'admins.delete';

    case DomainsViewAny = 'domains.viewAny';
    case DomainsView = 'domains.view';
    case DomainsCreate = 'domains.create';
    case DomainsUpdate = 'domains.update';
    case DomainsDelete = 'domains.delete';

    /**
     * @return list<self>
     */
    public static function forAdminRole(): array
    {
        return [
            self::UsersViewAny,
            self::UsersView,
            self::UsersCreate,
            self::UsersUpdate,
            self::UsersDelete,
            self::ActivityLogsViewAny,
            self::ActivityLogsView,
        ];
    }

    /**
     * @return list<self>
     */
    public static function forOperatorRole(): array
    {
        return [
            self::DomainsViewAny,
            self::DomainsView,
            self::DomainsCreate,
            self::DomainsUpdate,
            self::DomainsDelete,
        ];
    }
}
