<?php

namespace MailerSend\Tests\Helpers;

use MailerSend\Helpers\BuildUri;
use MailerSend\Tests\TestCase;

class BuildUriTest extends TestCase
{
    /** @dataProvider build_uri_provider */
    public function test_execute_build_uri(array $options, string $path, array $params, string $expected): void
    {
        $build_uri = (new BuildUri($options))->execute($path, $params);

        $this->assertEquals($expected, $build_uri);
    }

    public function build_uri_provider(): array
    {
        return [
            [
                [
                    'host' => 'api.mailersend.com',
                    'protocol' => 'https',
                    'api_path' => 'v1',
                ],
                'endpoint',
                [],
                'https://api.mailersend.com/v1/endpoint'
            ],
            [
                [
                    'host' => 'mailersend.local',
                    'protocol' => 'http',
                    'api_path' => 'api/v1',
                ],
                'endpoint',
                [],
                'http://mailersend.local/api/v1/endpoint'
            ],
            [
                [
                    'host' => 'mailersend.local',
                    'protocol' => 'http',
                    'api_path' => 'api/v1',
                ],
                'endpoint',
                [
                    'first' => 'param',
                    'second' => 'param'
                ],
                'http://mailersend.local/api/v1/endpoint?first=param&second=param',
            ]
        ];
    }
}
