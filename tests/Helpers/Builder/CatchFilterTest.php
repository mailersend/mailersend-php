<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Common\Constants;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\CatchFilter;
use MailerSend\Helpers\Builder\Filter;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class CatchFilterTest extends TestCase
{
    /**
     * @dataProvider invalidTypeProvider
     */
    #[DataProvider('invalidTypeProvider')]
    public function test_rejects_invalid_type(string $type): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('CatchFilter type must be one of: catch_all, catch_recipient.');

        new CatchFilter($type);
    }

    public static function invalidTypeProvider(): array
    {
        return [
            'unknown type' => ['invalid_type'],
            'match_all is not a catch type' => [Constants::TYPE_MATCH_ALL],
            'empty string' => [''],
        ];
    }

    public function test_add_filter_rejects_more_than_5_filters(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('CatchFilter filters cannot contain more than 5 items.');

        $filter = new CatchFilter(Constants::TYPE_CATCH_RECIPIENT);

        for ($i = 0; $i < 6; $i++) {
            $filter->addFilter(new Filter(Constants::COMPARER_EQUAL, 'value'));
        }
    }

    public function test_set_filters_rejects_more_than_5_filters(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('CatchFilter filters cannot contain more than 5 items.');

        $filters = [];
        for ($i = 0; $i < 6; $i++) {
            $filters[] = new Filter(Constants::COMPARER_EQUAL, 'value');
        }

        (new CatchFilter(Constants::TYPE_CATCH_RECIPIENT))->setFilters($filters);
    }
}
