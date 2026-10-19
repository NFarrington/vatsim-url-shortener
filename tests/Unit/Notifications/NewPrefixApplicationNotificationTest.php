<?php

namespace Tests\Unit\Notifications;

use App\Entities\OrganizationPrefixApplication;
use App\Entities\User;
use App\Notifications\NewPrefixApplicationNotification;
use Tests\Traits\RefreshDatabase;
use Tests\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\App\Notifications\NewPrefixApplicationNotification::class)]
class NewPrefixApplicationNotificationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function routes_notification_via_mail()
    {
        $user = make(User::class);
        $application = make(OrganizationPrefixApplication::class);
        $notification = new NewPrefixApplicationNotification($application);

        $routes = $notification->via($user);

        $this->assertContains('mail', $routes);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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
