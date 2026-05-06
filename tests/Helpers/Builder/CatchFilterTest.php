<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Helpers\Builder\CatchFilter;
use MailerSend\Helpers\Builder\Filter;
use PHPUnit\Framework\TestCase;

class CatchFilterTest extends TestCase
{
    public function test_filters_absent_when_empty(): void
    {
        $filter = new CatchFilter('all');

        $result = $filter->toArray();

        self::assertArrayNotHasKey('filters', $result);
    }

    public function test_filters_included_when_set(): void
    {
        $filter = new CatchFilter('all');
        $filter->addFilter(new Filter('contains', 'hello'));

        $result = $filter->toArray();

        self::assertArrayHasKey('filters', $result);
        self::assertCount(1, $result['filters']);
    }
}
