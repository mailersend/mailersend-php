<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\Filter;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class FilterTest extends TestCase
{
    /**
     * @dataProvider invalidComparerProvider
     */
    #[DataProvider('invalidComparerProvider')]
    public function test_rejects_invalid_comparer(string $comparer): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage(
            'Filter comparer must be one of: equal, not-equal, contains, not-contains, starts-with, ends-with, not-starts-with, not-ends-with.'
        );

        new Filter($comparer, 'test@example.com');
    }

    public static function invalidComparerProvider(): array
    {
        return [
            'unknown comparer' => ['invalid_comparer'],
            'empty string' => [''],
            'partial match' => ['equal_extra'],
        ];
    }
}
