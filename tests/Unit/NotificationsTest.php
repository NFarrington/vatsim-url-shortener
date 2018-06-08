<?php

namespace Tests\Unit;

use App\Models\OrganizationPrefixApplication;
use App\Models\User;
use App\Notifications\NewPrefixApplicationNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_verification_notification()
    {
        $token = str_random(40);
        $notification = new VerifyEmailNotification($token);
        $mail = $notification->toMail(make(User::class));
        $db = $notification->toArray(make(User::class));

        $this->assertEquals(['database', 'mail'], $notification->via(make(User::class)));

        $this->assertContains('Verify Email Address', $mail->subject);
        $this->assertArraySubset(['old_email', 'new_email'], array_keys($db));
    }

    /** @test */
    public function new_prefix_application_notification()
    {
        $application = make(OrganizationPrefixApplication::class);
        $notification = new NewPrefixApplicationNotification($application);
        $mail = $notification->toMail(make(User::class));

        $this->assertEquals(['mail'], $notification->via(make(User::class)));

        $this->assertContains('New Prefix Application', $mail->subject);
    }
}
