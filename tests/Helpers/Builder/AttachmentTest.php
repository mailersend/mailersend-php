<?php

namespace MailerSend\Tests\Helpers\Builder;

use Illuminate\Support\Arr;
use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Tests\TestCase;

class AttachmentTest extends TestCase
{
    public function test_pass_base64_content(): void
    {
        $base64_content = base64_encode('content');
        $attachment = (new Attachment($base64_content, 'filename.test'))->toArray();

        self::assertEquals($base64_content, Arr::get($attachment, 'content'));
        self::assertEquals('filename.test', Arr::get($attachment, 'filename'));
    }

    public function test_pass_non_base64_content(): void
    {
        $attachment = (new Attachment('content', 'filename.test'))->toArray();

        self::assertEquals(base64_encode('content'), Arr::get($attachment, 'content'));
        self::assertEquals('filename.test', Arr::get($attachment, 'filename'));
    }
}
