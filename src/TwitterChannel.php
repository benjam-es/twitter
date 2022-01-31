<?php

declare(strict_types=1);

namespace NotificationChannels\Twitter;

use Abraham\TwitterOAuth\Response;
use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twitter\Exceptions\CouldNotSendNotification;

final class TwitterChannel
{
    protected TwitterOAuth $twitter;

    public function __construct(TwitterOAuth $twitter)
    {
        $this->twitter = $twitter;
    }

    /**
     * Send the given notification.
     *
     * @throws CouldNotSendNotification
     */
    public function send(mixed $notifiable, Notification $notification): Response
    {
        $this->changeTwitterSettingsIfNeeded($notifiable);

        $twitterMessage = $notification->toTwitter($notifiable);
        $twitterMessage = $this->addImagesIfGiven($twitterMessage);

        $twitterApiResponse = $this->twitter->post(
            $twitterMessage->getApiEndpoint(),
            $twitterMessage->getRequestBody($this->twitter),
            $twitterMessage->isJsonRequest
        );

        if ($this->twitter->getLastHttpCode() !== 200) {
            throw CouldNotSendNotification::serviceRespondsNotSuccessful($this->twitter->getLastBody());
        }

        return $twitterApiResponse;
    }

    /**
     * Use per user settings instead of default ones.
     */
    private function changeTwitterSettingsIfNeeded(mixed $notifiable): void
    {
        if (method_exists($notifiable, 'routeNotificationFor') && $twitterSettings = $notifiable->routeNotificationFor('twitter')) {
            $this->twitter = new TwitterOAuth(
                $twitterSettings[0],
                $twitterSettings[1],
                $twitterSettings[2],
                $twitterSettings[3]
            );
        }
    }

    /**
     * If it is a status update message and images are provided, add them.
     */
    private function addImagesIfGiven(mixed $twitterMessage): mixed
    {
        if (is_a($twitterMessage, TwitterStatusUpdate::class) && $twitterMessage->getImages()) {
            $this->twitter->setTimeouts(10, 15);

            $twitterMessage->imageIds = collect($twitterMessage->getImages())->map(function (TwitterImage $image) {
                $media = $this->twitter->upload('media/upload', ['media' => $image->getPath()]);

                return (object) $media->media_id_string;
            });
        }

        return $twitterMessage;
    }
}
