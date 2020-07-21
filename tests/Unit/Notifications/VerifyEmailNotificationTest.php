<?php

namespace Tests\Unit\Notifications;

use App\Entities\User;
use App\Notifications\VerifyEmailNotification;
use Tests\Traits\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @covers \App\Notifications\VerifyEmailNotification
 */
class VerifyEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function routes_notification_via_database_and_mail()
    {
        $user = make(User::class);
        $notification = new VerifyEmailNotification(Str::random(40), 'new@example.com', 'old@example.com');

        $routes = $notification->via($user);

        $this->assertEquals(['mail'], $routes);
    }

    /** @test */
    public function converts_to_mail_notification()
    {
        $user = make(User::class);
        $notification = new VerifyEmailNotification(Str::random(40), 'new@example.com', 'old@example.com');

        $mail = $notification->toMail($user);

        $this->assertStringContainsString('Verify Email Address', $mail->subject);
    }
}
