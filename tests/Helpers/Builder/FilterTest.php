<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Helpers\Builder\Filter;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function test_key_absent_when_not_set(): void
    {
        $filter = new Filter('contains', 'hello');

        $result = $filter->toArray();

        self::assertArrayNotHasKey('key', $result);
    }

    public function test_key_included_when_set(): void
    {
        $filter = new Filter('contains', 'hello', 'subject');

        $result = $filter->toArray();

        self::assertArrayHasKey('key', $result);
        self::assertSame('subject', $result['key']);
    }
}
