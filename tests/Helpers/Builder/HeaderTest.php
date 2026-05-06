<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Helpers\Arr;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\Header;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class HeaderTest extends TestCase
{
    /**
     * @dataProvider invalidHeaderProvider
     * @param string $name
     * @param string $value
     * @param string $exceptionMessage
     */
    #[DataProvider('invalidHeaderProvider')]
    public function test_rejects_invalid_params(string $name, string $value, string $exceptionMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new Header($name, $value);
    }

    public static function invalidHeaderProvider(): array
    {
        return [
            'empty name' => [
                '',
                'Value 1',
                'Value "" is empty, but non empty value was expected.',
            ],
            'empty value' => [
                'Custom-Header-1',
                '',
                'Value "" is empty, but non empty value was expected.',
            ],
        ];
    }
}
