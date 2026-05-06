<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Helpers\Arr;
use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Tests\TestCase;

class AttachmentTest extends TestCase
{
    public function test_plain_text_content_is_base64_encoded(): void
    {
        $attachment = (new Attachment('content', 'filename.test'))->toArray();

        self::assertEquals(base64_encode('content'), Arr::get($attachment, 'content'));
        self::assertEquals('filename.test', Arr::get($attachment, 'filename'));
    }

    public function test_already_base64_content_is_not_double_encoded(): void
    {
        $base64Content = base64_encode('content');
        $attachment = (new Attachment($base64Content, 'filename.test'))->toArray();

        self::assertEquals($base64Content, Arr::get($attachment, 'content'));
        self::assertEquals('filename.test', Arr::get($attachment, 'filename'));
    }

    public function test_disposition_is_null_when_not_set(): void
    {
        $attachment = (new Attachment('content', 'filename.test'))->toArray();

        self::assertNull(Arr::get($attachment, 'disposition'));
    }

    public function test_id_is_null_when_not_set(): void
    {
        $attachment = (new Attachment('content', 'filename.test'))->toArray();

        self::assertNull(Arr::get($attachment, 'id'));
    }

}
