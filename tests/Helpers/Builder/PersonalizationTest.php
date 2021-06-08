<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Tests\TestCase;
use Tightenco\Collect\Support\Arr;

class PersonalizationTest extends TestCase
{
    public function test_personalization_validates_email(): void
    {
        $this->expectException(MailerSendAssertException::class);

        new Variable('testmailersend.com', []);
    }

    public function test_personalization_validates_substitutions_length(): void
    {
        $this->expectException(MailerSendAssertException::class);

        new Variable('test@mailersend.com', []);
    }

    public function test_creates_personalization(): void
    {
        $var = (new \MailerSend\Helpers\Builder\Personalization('test@mailersend.com', [
            [
                'var' => 'variable',
                'number' => 123,
                'object' => [
                    'key' => 'object-value'
                ],
                'objectCollection' => [
                    [
                        'name' => 'John'
                    ],
                    [
                        'name' => 'Patrick'
                    ]
                ],
            ]
        ]))->toArray();

        self::assertEquals('test@mailersend.com', Arr::get($var, 'email'));
        self::assertEquals('variable', Arr::get($var, 'data.0.var'));
        self::assertEquals(123, Arr::get($var, 'data.0.number'));
        self::assertEquals('object-value', Arr::get($var, 'data.0.object.key'));
        self::assertEquals('John', Arr::get($var, 'data.0.objectCollection.0.name'));
        self::assertEquals('Patrick', Arr::get($var, 'data.0.objectCollection.1.name'));
    }
}
