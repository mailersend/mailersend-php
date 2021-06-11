<?php

namespace MailerSend\Common;

class Constants
{
    public const SDK_VERSION = 'v0.1';
    public const POSSIBLE_EVENT_TYPES = ['processed', 'queued', 'sent', 'delivered', 'soft_bounced', 'hard_bounced', 'junk', 'opened', 'clicked', 'unsubscribed', 'spam_complaints'];
    public const POSSIBLE_GROUP_BY_OPTIONS = ['days', 'weeks', 'months', 'years'];
}
