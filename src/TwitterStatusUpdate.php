<?php

declare(strict_types=1);

namespace NotificationChannels\Twitter;

use Kylewm\Brevity\Brevity;
use Illuminate\Support\Collection;
use NotificationChannels\Twitter\Exceptions\CouldNotSendNotification;

final class TwitterStatusUpdate
{
    public bool $isJsonRequest = false;

    /**
     * @var Collection <int, iterable>
     */
    public Collection $imageIds;

    protected string $content;

    private array $images = [];

    private string $apiEndpoint = 'statuses/update';

    /**
     * TwitterStatusUpdate constructor.
     *
     * @throws CouldNotSendNotification
     */
    public function __construct(string $content)
    {
        if ($exceededLength = $this->messageIsTooLong($content, new Brevity())) {
            throw CouldNotSendNotification::statusUpdateTooLong($exceededLength);
        }

        $this->content = $content;
    }

    /**
     * Set Twitter media files.
     *
     * @param array|string $images
     */
    public function withImage($images): self
    {
        collect($images)->each(function ($image): void {
            $this->images[] = new TwitterImage($image);
        });

        return $this;
    }

    /**
     * Get Twitter status update content.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Get Twitter images list.
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * Return Twitter status update api endpoint.
     *
     * @return string
     */
    public function getApiEndpoint(): string
    {
        return $this->apiEndpoint;
    }

    /**
     * Build Twitter request body.
     */
    public function getRequestBody(): array
    {
        $body = [
            'status' => $this->getContent(),
        ];

        if (count($this->imageIds) > 0) {
            $body['media_ids'] = $this->imageIds->implode(',');
        }

        return $body;
    }

    /**
     * Check if the message length is too long.
     */
    private function messageIsTooLong(string $content, Brevity $brevity): int
    {
        $tweetLength = $brevity->tweetLength($content);
        $exceededLength = $tweetLength - 280;

        return $exceededLength > 0 ? $exceededLength : 0;
    }
}
