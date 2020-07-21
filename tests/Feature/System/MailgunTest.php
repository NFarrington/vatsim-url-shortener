<?php

namespace Tests\Feature\Site;

use App\Entities\EmailEvent;
use App\Entities\SystemUser;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Tests\Traits\RefreshDatabase;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tests\TestCase;

class MailgunTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function mailgun_webhook_records_event()
    {
        $user = create(SystemUser::class);

        $timestamp = mt_rand();
        $token = Str::random();
        $signature = hash_hmac('sha256', $timestamp.$token, env('MAILGUN_SECRET'));
        $this->post(route('system.mailgun'), [
            'timestamp' => $timestamp,
            'token' => $token,
            'signature' => $signature,
            'domain' => 'vats.im',
            'message-headers' => '[["Received", "by luna.mailgun.net with SMTP mgrt 8734663311733; Fri, 03 May 2013 18:26:27 +0000"], ["Content-Type", ["multipart/alternative", {"boundary": "eb663d73ae0a4d6c9153cc0aec8b7520"}]], ["Mime-Version", "1.0"], ["Subject", "Test deliver webhook"], ["From", "Bob <bob@vats.im>"], ["To", "Alice <alice@example.com>"], ["Message-Id", "<20130503182626.18666.16540@vats.im>"], ["X-Mailgun-Variables", "{\"my_var_1\": \"Mailgun Variable #1\", \"my-var-2\": \"awesome\"}"], ["Date", "Fri, 03 May 2013 18:26:27 +0000"], ["Sender", "bob@vats.im"]]',
            'Message-Id' => '<20130503182626.18666.16540@vats.im>',
            'recipient' => 'alice@example.com',
            'event' => 'delivered',
        ], [
            'PHP_AUTH_USER' => $user->getUsername(),
            'PHP_AUTH_PW' => 'secret',
        ])->assertStatus(200);

        $this->assertDatabaseHas(EntityManager::getClassMetadata(EmailEvent::class)->getTableName(), [
            'message_id' => '<20130503182626.18666.16540@vats.im>',
        ]);
    }

    /** @test */
    function invalid_credentials_fail_with_unauthorized_error()
    {
        $this->withExceptionHandling();
        $user = create(SystemUser::class);

        $timestamp = mt_rand();
        $token = Str::random();
        $signature = hash_hmac('sha256', $timestamp.$token, env('MAILGUN_SECRET'));
        $this->post(route('system.mailgun'), [
            'timestamp' => $timestamp,
            'token' => $token,
            'signature' => $signature,
            'domain' => 'vats.im',
            'message-headers' => '[["Received", "by luna.mailgun.net with SMTP mgrt 8734663311733; Fri, 03 May 2013 18:26:27 +0000"], ["Content-Type", ["multipart/alternative", {"boundary": "eb663d73ae0a4d6c9153cc0aec8b7520"}]], ["Mime-Version", "1.0"], ["Subject", "Test deliver webhook"], ["From", "Bob <bob@vats.im>"], ["To", "Alice <alice@example.com>"], ["Message-Id", "<20130503182626.18666.16540@vats.im>"], ["X-Mailgun-Variables", "{\"my_var_1\": \"Mailgun Variable #1\", \"my-var-2\": \"awesome\"}"], ["Date", "Fri, 03 May 2013 18:26:27 +0000"], ["Sender", "bob@vats.im"]]',
            'Message-Id' => '<20130503182626.18666.16540@vats.im>',
            'recipient' => 'alice@example.com',
            'event' => 'delivered',
        ], [
            'PHP_AUTH_USER' => $user->getUsername(),
            'PHP_AUTH_PW' => 'not-secret',
        ])->assertUnauthorized();

        $this->assertDatabaseMissing(EntityManager::getClassMetadata(EmailEvent::class)->getTableName(), [
            'message_id' => '<20130503182626.18666.16540@vats.im>',
        ]);
    }

    /** @test */
    function invalid_signature_fails_with_406_error()
    {
        $user = create(SystemUser::class);

        $timestamp = mt_rand();
        $token = Str::random();
        $signature = hash_hmac('sha256', $timestamp.$token, Str::random());
        $this->post(route('system.mailgun'), [
            'timestamp' => $timestamp,
            'token' => $token,
            'signature' => $signature,
            'domain' => 'vats.im',
            'message-headers' => '[["Received", "by luna.mailgun.net with SMTP mgrt 8734663311733; Fri, 03 May 2013 18:26:27 +0000"], ["Content-Type", ["multipart/alternative", {"boundary": "eb663d73ae0a4d6c9153cc0aec8b7520"}]], ["Mime-Version", "1.0"], ["Subject", "Test deliver webhook"], ["From", "Bob <bob@vats.im>"], ["To", "Alice <alice@example.com>"], ["Message-Id", "<20130503182626.18666.16540@vats.im>"], ["X-Mailgun-Variables", "{\"my_var_1\": \"Mailgun Variable #1\", \"my-var-2\": \"awesome\"}"], ["Date", "Fri, 03 May 2013 18:26:27 +0000"], ["Sender", "bob@vats.im"]]',
            'Message-Id' => '<20130503182626.18666.16540@vats.im>',
            'recipient' => 'alice@example.com',
            'event' => 'delivered',
        ], [
            'PHP_AUTH_USER' => $user->getUsername(),
            'PHP_AUTH_PW' => 'secret',
        ])->assertStatus(406);

        $this->assertDatabaseMissing(EntityManager::getClassMetadata(EmailEvent::class)->getTableName(), [
            'message_id' => '<20130503182626.18666.16540@vats.im>',
        ]);
    }
}
