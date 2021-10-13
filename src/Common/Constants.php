<?php

namespace MailerSend\Common;

class Constants
{
    public const SDK_VERSION = 'v0.4.0';
    public const DEFAULT_LIMIT = 25;
    public const MIN_LIMIT = 10;
    public const MAX_LIMIT = 100;
    public const POSSIBLE_EVENT_TYPES = ['processed', 'queued', 'sent', 'delivered', 'soft_bounced', 'hard_bounced', 'junk', 'opened', 'clicked', 'unsubscribed', 'spam_complaints'];
    public const POSSIBLE_GROUP_BY_OPTIONS = ['days', 'weeks', 'months', 'years'];
    public const GROUP_BY_DAYS = 'days';
    public const GROUP_BY_WEEKS = 'weeks';
    public const GROUP_BY_MONTHS = 'months';
    public const GROUP_BY_YEARS = 'years';
}
