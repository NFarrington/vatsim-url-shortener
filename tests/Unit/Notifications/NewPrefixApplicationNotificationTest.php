<?php

namespace Tests\Unit\Notifications;

use App\Entities\OrganizationPrefixApplication;
use App\Entities\User;
use App\Notifications\NewPrefixApplicationNotification;
use Tests\Traits\RefreshDatabase;
use Tests\TestCase;

/**
 * @covers \App\Notifications\NewPrefixApplicationNotification
 */
class NewPrefixApplicationNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function routes_notification_via_mail()
    {
        $user = make(User::class);
        $application = make(OrganizationPrefixApplication::class);
        $notification = new NewPrefixApplicationNotification($application);

        $routes = $notification->via($user);

        $this->assertContains('mail', $routes);
    }

    /** @test */
    public function converts_to_mail_notification()
    {
        $user = make(User::class);
        $application = make(OrganizationPrefixApplication::class);
        $notification = new NewPrefixApplicationNotification($application);

        $mail = $notification->toMail($user);

        $this->assertStringContainsString('New Prefix Application', $mail->subject);
        $this->assertContains("A new prefix application has been submitted for {$application->getOrganization()->getName()}.",
            $mail->introLines);
    }
}
