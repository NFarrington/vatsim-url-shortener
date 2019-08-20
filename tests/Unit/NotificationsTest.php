<?php

namespace Tests\Unit;

use App\Models\OrganizationPrefixApplication;
use App\Models\User;
use App\Notifications\NewPrefixApplicationNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_verification_notification()
    {
        $token = Str::random(40);
        $notification = new VerifyEmailNotification($token);
        $mail = $notification->toMail(make(User::class));
        $db = $notification->toArray(make(User::class));

        $this->assertEquals(['database', 'mail'], $notification->via(make(User::class)));

        $this->assertStringContainsString('Verify Email Address', $mail->subject);
        $this->assertArrayHasKey('old_email', $db);
        $this->assertArrayHasKey('new_email', $db);
    }

    /** @test */
    public function new_prefix_application_notification()
    {
        $application = make(OrganizationPrefixApplication::class);
        $notification = new NewPrefixApplicationNotification($application);
        $mail = $notification->toMail(make(User::class));

        $this->assertEquals(['mail'], $notification->via(make(User::class)));

        $this->assertStringContainsString('New Prefix Application', $mail->subject);
    }
}
