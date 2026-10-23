<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Common\Constants;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\Forward;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ForwardTest extends TestCase
{
    /**
     * @dataProvider invalidForwardProvider
     */
    #[DataProvider('invalidForwardProvider')]
    public function test_rejects_invalid_params(string $type, string $value, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        new Forward($type, $value);
    }

    public static function invalidForwardProvider(): array
    {
        return [
            'unknown type' => ['invalid_type', 'https://example.com', 'Forward type must be one of: email, webhook.'],
            'empty type' => ['', 'https://example.com', 'Forward type must be one of: email, webhook.'],
            'ftp type' => ['ftp', 'https://example.com', 'Forward type must be one of: email, webhook.'],
            'value longer than 191 characters' => [
                Constants::TYPE_WEBHOOK,
                'https://example.com/' . str_repeat('a', 180),
                'Forward value cannot be longer than 191 characters.',
            ],
            'webhook value not a url' => [
                Constants::TYPE_WEBHOOK,
                'not-a-valid-url',
                'Forward value must be a valid URL when type is webhook.',
            ],
            'email value not an email address' => [
                Constants::TYPE_EMAIL,
                'not-a-valid-email',
                'Forward value must be a valid email address when type is email.',
            ],
        ];
    }
}
