<?php

namespace MailerSend\Common;

class Constants
{
    public const SDK_VERSION = 'v0.1';
    public const MIN_LIMIT = 10;
    public const MAX_LIMIT = 100;
    public const POSSIBLE_EVENT_TYPES = ['processed', 'queued', 'sent', 'delivered', 'soft_bounced', 'hard_bounced', 'junk', 'opened', 'clicked', 'unsubscribed', 'spam_complaints'];
}
