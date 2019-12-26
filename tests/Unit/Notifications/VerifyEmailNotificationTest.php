<?php

namespace Tests\Unit\Notifications;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VerifyEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function routes_notification_via_database_and_mail()
    {
        $user = make(User::class);
        $notification = new VerifyEmailNotification(Str::random(40));

        $routes = $notification->via($user);

        $this->assertEquals(['database', 'mail'], $routes);
    }

    /** @test */
    public function converts_to_array()
    {
        $user = make(User::class);
        $notification = new VerifyEmailNotification(Str::random(40));

        $array = $notification->toArray($user);

        $this->assertArrayHasKey('old_email', $array);
        $this->assertArrayHasKey('new_email', $array);
    }

    /** @test */
    public function converts_to_mail_notification()
    {
        $user = make(User::class);
        $notification = new VerifyEmailNotification(Str::random(40));

        $mail = $notification->toMail($user);

        $this->assertStringContainsString('Verify Email Address', $mail->subject);
    }
}
