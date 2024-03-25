<?php

namespace MailerSend\Common;

class Permissions
{
    public const READ_ALL_TEMPLATES = 'read-all-templates';
    public const READ_OWN_TEMPLATES = 'read-own-templates';
    public const MANAGE_TEMPLATES = 'manage-template';
    public const READ_FILEMANAGER = 'read-filemanager';
    public const MANAGE_DOMAIN = 'manage-domain';
    public const MANAGE_INBOUND = 'manage-inbound';
    public const MANAGE_WEBHOOK = 'manage-webhook';
    public const CONTROL_SENDINGS = 'control-sendings';
    public const CONTROL_TRACKING_OPTIONS = 'control-tracking-options';
    public const ACCESS_SMTP_CREDENTIALS = 'access-smtp-credentials';
    public const VIEW_SMTP_USERS = 'view-smtp-users';
    public const MANAGE_SMTP_USERS = 'manage-smtp-users';
    public const READ_RECIPIENT = 'read-recipient';
    public const READ_ACTIVITY = 'read-activity';
    public const READ_EMAIL = 'read-email';
    public const READ_ANALYTICS = 'read-analytics';
    public const READ_SENDER_IDENTITIES = 'read-sender-identities';
    public const MANAGE_SENDER_IDENTITIES = 'manage-sender-identities';
    public const READ_EMAIL_VERIFICATION = 'read-email-verification';
    public const MANAGE_EMAIL_VERIFICATION = 'manage-email-verification';
    public const MANAGE_SMS = 'manage-sms';
    public const READ_SMS = 'read-sms';
    public const MANAGE_VERIFIED_RECIPIENTS = 'manage-verified-recipients';
    public const VIEW_SMS_WEBHOOKS = 'view-sms-webhooks';
    public const MANAGE_SMS_WEBHOOKS = 'manage-sms-webhooks';
    public const VIEW_SMS_INBOUND = 'view-sms-inbound';
    public const MANAGE_SMS_INBOUND = 'manage-sms-inbound';
    public const UPDATE_PLAN = 'update-plan';
    public const MANAGE_ACCOUNT = 'manage-account';
    public const READ_INVOICE = 'read-invoice';
    public const MANAGE_API_TOKEN = 'manage-api-token';
    public const READ_SUPPRESSIONS = 'read-suppressions';
    public const MANAGE_SUPPRESSIONS = 'manage-suppressions';
    public const READ_IP_ADDRESSES = 'read-ip-addresses';
    public const MANAGE_IP_ADDRESSES = 'manage-ip-addresses';
    public const READ_ERROR_LOG = 'read-error-log';
}
