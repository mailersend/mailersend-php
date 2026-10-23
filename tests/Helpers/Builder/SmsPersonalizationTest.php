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
     * @param string $expectedMessage
     */
    #[DataProvider('invalidSmsPersonalizationProvider')]
    public function test_rejects_invalid_params(string $recipient, array $data, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        new SmsPersonalization($recipient, $data);
    }

    public static function invalidSmsPersonalizationProvider(): array
    {
        return [
            'recipient without leading plus' => [
                '15551234567',
                ['name' => 'John'],
                'Value "15551234567" does not start with "+".',
            ],
            'empty data array' => [
                '+15551234567',
                [],
                'List should have at least 1 elements, but has 0 elements.',
            ],
        ];
    }
}
