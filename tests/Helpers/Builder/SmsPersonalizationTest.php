<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\SmsPersonalization;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class SmsPersonalizationTest extends TestCase
{
    /**
     * @dataProvider invalidSmsPersonalizationProvider
     * @param string $recipient
     * @param array $data
     */
    #[DataProvider('invalidSmsPersonalizationProvider')]
    public function test_rejects_invalid_params(string $recipient, array $data): void
    {
        $this->expectException(MailerSendAssertException::class);

        new SmsPersonalization($recipient, $data);
    }

    public static function invalidSmsPersonalizationProvider(): array
    {
        return [
            'recipient without leading plus' => [
                '15551234567',
                [['var' => 'name', 'value' => 'John']],
            ],
            'empty data array' => [
                '+15551234567',
                [],
            ],
        ];
    }
}
