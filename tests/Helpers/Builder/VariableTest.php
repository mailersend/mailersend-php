<?php

namespace MailerSend\Tests\Helpers\Builder;

use Illuminate\Support\Arr;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\Variable;
use MailerSend\Tests\TestCase;

class VariableTest extends TestCase
{
    public function test_variable_validates_email(): void
    {
        $this->expectException(MailerSendAssertException::class);

        new Variable('testmailersend.com', []);
    }

    public function test_variable_validates_substitutions_length(): void
    {
        $this->expectException(MailerSendAssertException::class);

        new Variable('test@mailersend.com', []);
    }

    public function test_creates_variable(): void
    {
        $var = (new Variable('test@mailersend.com', [
            'var' => 'value',
            'var2' => 'value2',
        ]))->toArray();

        self::assertEquals('test@mailersend.com', Arr::get($var, 'email'));
        self::assertEquals('var', Arr::get($var, 'substitutions.0.var'));
        self::assertEquals('value', Arr::get($var, 'substitutions.0.value'));
        self::assertEquals('var2', Arr::get($var, 'substitutions.1.var'));
        self::assertEquals('value2', Arr::get($var, 'substitutions.1.value'));
    }
}
