<?php

namespace MailerSend\Common;

use Composer\InstalledVersions;

class SdkVersion
{
    public const PACKAGE_NAME = 'mailersend/mailersend';

    // The repository stores no version: Composer records the installed one from the git tag.
    public static function get(): string
    {
        if (!class_exists(InstalledVersions::class) || !InstalledVersions::isInstalled(self::PACKAGE_NAME)) {
            return 'unknown';
        }

        return InstalledVersions::getPrettyVersion(self::PACKAGE_NAME) ?? 'unknown';
    }
}
