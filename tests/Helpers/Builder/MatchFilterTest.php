<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Helpers\Builder\Filter;
use MailerSend\Helpers\Builder\MatchFilter;
use PHPUnit\Framework\TestCase;

class MatchFilterTest extends TestCase
{
    public function test_filters_absent_when_empty(): void
    {
        $filter = new MatchFilter('any');

        $result = $filter->toArray();

        self::assertArrayNotHasKey('filters', $result);
    }

    public function test_filters_included_when_set(): void
    {
        $filter = new MatchFilter('any');
        $filter->addFilter(new Filter('contains', 'world'));

        $result = $filter->toArray();

        self::assertArrayHasKey('filters', $result);
        self::assertCount(1, $result['filters']);
    }
}
