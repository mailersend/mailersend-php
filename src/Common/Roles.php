<?php

namespace MailerSend\Common;

class Roles
{
    public const ADMIN = 'Admin';
    public const MANAGER = 'Manager';
    public const DESIGNER = 'Designer';
    public const ACCOUNTANT = 'Accountant';
    public const CUSTOM_USER = 'Custom User';

    public const ALL_ROLES = [
        self::ADMIN,
        self::MANAGER,
        self::DESIGNER,
        self::ACCOUNTANT,
        self::CUSTOM_USER,
    ];
}
