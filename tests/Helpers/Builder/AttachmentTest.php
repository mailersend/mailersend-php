<?php

namespace MailerSend\Tests\Helpers\Builder;

use MailerSend\Exceptions\MailerSendAssertException;
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

    public function test_disposition_rejects_invalid_value(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Disposition must be either "inline" or "attachment".');

        $attachment = new Attachment('content', 'filename.test');
        $attachment->setDisposition('foobar');
    }

    public function test_disposition_accepts_inline(): void
    {
        $attachment = new Attachment('content', 'filename.test', 'inline');

        self::assertEquals('inline', Arr::get($attachment->toArray(), 'disposition'));
    }

    public function test_disposition_accepts_attachment(): void
    {
        $attachment = new Attachment('content', 'filename.test', 'attachment');

        self::assertEquals('attachment', Arr::get($attachment->toArray(), 'disposition'));
    }

    public function test_id_rejects_value_exceeding_256_chars(): void
    {
        $this->expectException(MailerSendAssertException::class);
        $this->expectExceptionMessage('Attachment id may not be greater than 256 characters.');

        $attachment = new Attachment('content', 'filename.test');
        $attachment->setId(str_repeat('a', 257));
    }

    public function test_id_accepts_value_of_exactly_256_chars(): void
    {
        $id = str_repeat('a', 256);
        $attachment = new Attachment('content', 'filename.test', null, $id);

        self::assertEquals($id, Arr::get($attachment->toArray(), 'id'));
    }

}
