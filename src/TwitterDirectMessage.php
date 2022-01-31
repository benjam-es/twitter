<?php

declare(strict_types=1);

namespace NotificationChannels\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;
use NotificationChannels\Twitter\Exceptions\CouldNotSendNotification;

final class TwitterDirectMessage
{
    public bool $isJsonRequest = true;

    private string $content;

    private mixed $to;

    private string $apiEndpoint = 'direct_messages/events/new';

    /**
     * TwitterDirectMessage constructor.
     */
    public function __construct(mixed $to, string $content)
    {
        $this->to = $to;
        $this->content = $content;
    }

    /**
     * Get Twitter direct message content.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Get Twitter direct message receiver.
     *
     * @throws CouldNotSendNotification
     */
    public function getReceiver(TwitterOAuth $twitter): mixed
    {
        if (is_int($this->to)) {
            return $this->to;
        }

        $user = $twitter->get('users/show', [
            'screen_name' => $this->to,
            'include_user_entities' => false,
            'skip_status' => true,
        ]);

        if ($twitter->getLastHttpCode() === 404) {
            throw CouldNotSendNotification::userWasNotFound($twitter->getLastBody());
        }

        return $user->id;
    }

    /**
     * Return Twitter direct message api endpoint.
     */
    public function getApiEndpoint(): string
    {
        return $this->apiEndpoint;
    }

    /**
     * Build Twitter request body.
     *
     * @throws CouldNotSendNotification
     */
    public function getRequestBody(TwitterOAuth $twitter): array
    {
        return [
            'event' => [
                'type' => 'message_create',
                'message_create' => [
                    'target' => [
                        'recipient_id' => $this->getReceiver($twitter),
                    ],
                    'message_data' => [
                        'text' => $this->getContent(),
                    ],
                ],
            ],
        ];
    }
}
