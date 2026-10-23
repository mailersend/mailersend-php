<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Common\Constants;
use MailerSend\Exceptions\MailerSendAssertException;
use MailerSend\Helpers\Builder\Forward;
use MailerSend\Helpers\Builder\Inbound;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class InboundTest extends TestCase
{
    /**
     * @dataProvider includeAttachmentsProvider
     */
    #[DataProvider('includeAttachmentsProvider')]
    public function test_to_array_includes_include_attachments_when_set(bool $includeAttachments): void
    {
        $inbound = (new Inbound('domainId', 'name', false))->setIncludeAttachments($includeAttachments);

        self::assertSame($includeAttachments, $inbound->toArray()['include_attachments']);
    }

    public static function includeAttachmentsProvider(): array
    {
        return [
            'include attachments' => [true],
            'exclude attachments' => [false],
        ];
    }

    public function test_to_array_omits_optional_fields_that_were_not_set(): void
    {
        $array = (new Inbound('domainId', 'name', false))->toArray();

        $this->assertBodyExcludes([
            'inbound_domain',
            'catch_filter',
            'match_filter',
            'catch_type',
            'match_type',
            'inbound_priority',
            'include_attachments',
        ], $array);
    }

    public function test_add_forward_rejects_more_than_5_forwards(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Forwards cannot contain more than 5 items.');

        $builder = new Inbound('domainId', 'name', true);

        for ($i = 0; $i < 6; $i++) {
            $builder->addForward(new Forward(Constants::TYPE_WEBHOOK, 'https://example.com'));
        }
    }

    /**
     * @dataProvider invalidForwardsProvider
     */
    #[DataProvider('invalidForwardsProvider')]
    public function test_set_forwards_rejects_invalid_count(int $count, string $expectedMessage): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage($expectedMessage);

        $forwards = [];
        for ($i = 0; $i < $count; $i++) {
            $forwards[] = new Forward(Constants::TYPE_WEBHOOK, 'https://example.com');
        }

        (new Inbound('domainId', 'name', true))->setForwards($forwards);
    }

    public static function invalidForwardsProvider(): array
    {
        return [
            'no forwards' => [0, 'Forwards must contain at least 1 item.'],
            'more than 5 forwards' => [6, 'Forwards cannot contain more than 5 items.'],
        ];
    }
}
