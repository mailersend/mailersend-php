<?php

namespace MailerSend\Tests\Helpers;

use MailerSend\Helpers\BuildUri;
use MailerSend\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class BuildUriTest extends TestCase
{
    /**
     * @dataProvider buildUriProvider
     */
    #[DataProvider('buildUriProvider')]
    public function test_execute_build_uri(array $options, string $path, array $params, string $expected): void
    {
        $buildUri = (new BuildUri($options))->execute($path, $params);

        $this->assertEquals($expected, $buildUri);
    }

    public static function buildUriProvider(): array
    {
        return [
            'no query params' => [
                [
                    'host' => 'api.mailersend.com',
                    'protocol' => 'https',
                    'api_path' => 'v1',
                ],
                'endpoint',
                [],
                'https://api.mailersend.com/v1/endpoint',
            ],
            'custom protocol and host' => [
                [
                    'host' => 'mailersend.local',
                    'protocol' => 'http',
                    'api_path' => 'api/v1',
                ],
                'endpoint',
                [],
                'http://mailersend.local/api/v1/endpoint',
            ],
            'scalar query params' => [
                [
                    'host' => 'mailersend.local',
                    'protocol' => 'http',
                    'api_path' => 'api/v1',
                ],
                'endpoint',
                [
                    'first' => 'param',
                    'second' => 'param',
                ],
                'http://mailersend.local/api/v1/endpoint?first=param&second=param',
            ],
            'array query param is joined with commas' => [
                [
                    'host' => 'api.mailersend.com',
                    'protocol' => 'https',
                    'api_path' => 'v1',
                ],
                'endpoint',
                [
                    'ids' => ['aaa', 'bbb', 'ccc'],
                ],
                'https://api.mailersend.com/v1/endpoint?ids=aaa,bbb,ccc',
            ],
        ];
    }
}
