<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class PersonalizationTest extends TestCase
{
    /**
     * @dataProvider invalidPersonalizationProvider
     * @param string $email
     * @param array $data
     * @param string $exceptionMessage
     */
    #[DataProvider('invalidPersonalizationProvider')]
    public function test_rejects_invalid_params(string $email, array $data, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new Personalization($email, $data);
    }

    public static function invalidPersonalizationProvider(): array
    {
        return [
            'invalid email format' => [
                'testmailersend.com',
                [['var' => 'value']],
                'Value "testmailersend.com" was expected to be a valid e-mail address.',
            ],
            'empty data array' => [
                'test@mailersend.com',
                [],
                'List should have at least 1 elements, but has 0 elements.',
            ],
        ];
    }
}
