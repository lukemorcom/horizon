<?php

namespace Laravel\Horizon\Tests\Feature;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;
use Laravel\Horizon\Events\LongWaitDetected;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\Notifications\LongWaitDetected as LongWaitDetectedNotification;
use Laravel\Horizon\Tests\IntegrationTest;

class NotificationOverridesTest extends IntegrationTest
{
    public function test_custom_notifications_are_sent_if_specified()
    {
        Notification::fake();

        $customNotification = new class('redis', 'test-queue-2', 60) extends LongWaitDetectedNotification {
            public function toMail($notifiable)
            {
                return (new MailMessage)
                    ->line('This is a custom notification for a long wait.');
            }
        };

        Horizon::routeMailNotificationsTo('taylor@laravel.com');

        Horizon::overrideNotifications([
            LongWaitDetected::class => get_class($customNotification)
        ]);

        event(new LongWaitDetected('redis', 'test-queue-2', 60));

        Notification::assertSentOnDemand(get_class($customNotification));
    }
}
