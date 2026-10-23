<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Helpers\Builder\WhatsappPersonalization;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class WhatsappPersonalizationTest extends TestCase
{
    public function test_to_array_nests_sections_under_data(): void
    {
        $personalization = (new WhatsappPersonalization('19191234567'))
            ->setHeader(['John'])
            ->setBody(['order #1234', 'tomorrow'])
            ->setButtons(['orders/1234']);

        self::assertSame([
            'to' => '19191234567',
            'data' => [
                'header' => ['John'],
                'body' => ['order #1234', 'tomorrow'],
                'buttons' => ['orders/1234'],
            ],
        ], $personalization->toArray());
    }

    /**
     * @dataProvider emptySectionsProvider
     * @param WhatsappPersonalization $personalization
     * @param array $expectedData
     */
    #[DataProvider('emptySectionsProvider')]
    public function test_to_array_omits_empty_sections(WhatsappPersonalization $personalization, array $expectedData): void
    {
        self::assertSame([
            'to' => '19191234567',
            'data' => $expectedData,
        ], $personalization->toArray());
    }

    public static function emptySectionsProvider(): array
    {
        return [
            'only body set' => [
                (new WhatsappPersonalization('19191234567'))
                    ->setBody(['order #1234']),
                ['body' => ['order #1234']],
            ],
            'header and body set, buttons empty' => [
                (new WhatsappPersonalization('19191234567'))
                    ->setHeader(['John'])
                    ->setBody(['order #1234'])
                    ->setButtons([]),
                ['header' => ['John'], 'body' => ['order #1234']],
            ],
            'no sections set' => [
                new WhatsappPersonalization('19191234567'),
                [],
            ],
        ];
    }
}
