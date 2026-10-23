<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Common\Constants;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\Filter;
use MailerSend\Helpers\Builder\MatchFilter;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class MatchFilterTest extends TestCase
{
    /**
     * @dataProvider invalidTypeProvider
     */
    #[DataProvider('invalidTypeProvider')]
    public function test_rejects_invalid_type(string $type): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('MatchFilter type must be one of: match_all, match_sender, match_domain, match_header.');

        new MatchFilter($type);
    }

    public static function invalidTypeProvider(): array
    {
        return [
            'unknown type' => ['invalid_type'],
            'catch_all is not a match type' => [Constants::TYPE_CATCH_ALL],
            'empty string' => [''],
        ];
    }

    public function test_add_filter_rejects_more_than_5_filters(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('MatchFilter filters cannot contain more than 5 items.');

        $filter = new MatchFilter(Constants::TYPE_MATCH_SENDER);

        for ($i = 0; $i < 6; $i++) {
            $filter->addFilter(new Filter(Constants::COMPARER_EQUAL, 'value'));
        }
    }

    public function test_set_filters_rejects_more_than_5_filters(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('MatchFilter filters cannot contain more than 5 items.');

        $filters = [];
        for ($i = 0; $i < 6; $i++) {
            $filters[] = new Filter(Constants::COMPARER_EQUAL, 'value');
        }

        (new MatchFilter(Constants::TYPE_MATCH_SENDER))->setFilters($filters);
    }
}
