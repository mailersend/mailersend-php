<?php

namespace MailerSend\Common;

class Constants
{
    public const SDK_VERSION = 'v0.24.0';
    public const DEFAULT_LIMIT = 25;
    public const MIN_LIMIT = 10;
    public const MAX_LIMIT = 100;
    public const POSSIBLE_EVENT_TYPES = ['queued', 'sent', 'delivered', 'soft_bounced', 'hard_bounced', 'junk', 'opened', 'clicked', 'unsubscribed', 'spam_complaints'];
    public const POSSIBLE_SMS_STATUSES = ['queued', 'sent', 'delivered', 'failed'];
    public const POSSIBLE_SMS_RECIPIENT_STATUSES = ['active', 'opt_out'];
    public const POSSIBLE_GROUP_BY_OPTIONS = ['days', 'weeks', 'months', 'years'];
    public const GROUP_BY_DAYS = 'days';
    public const GROUP_BY_WEEKS = 'weeks';
    public const GROUP_BY_MONTHS = 'months';
    public const GROUP_BY_YEARS = 'years';

    // Inbound Filters
    public const TYPE_CATCH_ALL = 'catch_all';
    public const TYPE_CATCH_RECIPIENT = 'catch_recipient';
    public const TYPE_MATCH_ALL = 'match_all';
    public const TYPE_MATCH_SENDER = 'match_sender';
    public const TYPE_MATCH_DOMAIN = 'match_domain';
    public const TYPE_MATCH_HEADER = 'match_header';

    // Inbound Types
    public const CATCH_TYPE_ALL = 'all';
    public const CATCH_TYPE_ONE = 'one';
    public const MATCH_TYPE_ALL = 'all';
    public const MATCH_TYPE_ONE = 'one';

    // Comparison Operators
    public const COMPARER_EQUAL = 'equal';
    public const COMPARER_NOT_EQUQL = 'not-equal';
    public const COMPARER_CONTAINS = 'contains';
    public const COMPARER_NOT_CONTAINS = 'not-contains';
    public const COMPARER_STARTS_WITH = 'starts-with';
    public const COMPARER_ENDS_WITH = 'ends-with';
    public const COMPARER_NOT_STARTS_WITH = 'not-starts-with';
    public const COMPARER_NOT_ENDS_WITH = 'not-ends-with';

    // Forward Types
    public const TYPE_WEBHOOK = 'webhook';

    // Scheduled messages status
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_SENT = 'sent';
    public const STATUS_ERROR = 'error';

    public const SCHEDULED_MESSAGES_STATUSES = [
        self::STATUS_SCHEDULED,
        self::STATUS_SENT,
        self::STATUS_ERROR,
    ];
}
