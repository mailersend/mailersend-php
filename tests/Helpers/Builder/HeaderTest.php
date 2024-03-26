<?php

namespace MailerSend\Tests\Helpers\Builder;

use Illuminate\Support\Arr;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\Header;
use MailerSend\Tests\TestCase;

class HeaderTest extends TestCase
{
    public function test_properly_sets_header_params(): void
    {
        $header = (new Header('Custom-Header-1', 'Value 1'))->toArray();

        self::assertEquals('Custom-Header-1', Arr::get($header, 'name'));
        self::assertEquals('Value 1', Arr::get($header, 'value'));
    }

    public function test_header_validates_empty_name(): void
    {
        $this->expectException(MailerSendAssertException::class);

        (new Header('', 'Value 1'));
    }

    public function test_header_validates_empty_value(): void
    {
        $this->expectException(MailerSendAssertException::class);

        (new Header('Custom-Header-1', ''));
    }
}
