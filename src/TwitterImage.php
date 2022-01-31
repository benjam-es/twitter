<?php

declare(strict_types=1);

namespace NotificationChannels\Twitter;

final class TwitterImage
{
    private string $imagePath;

    public function __construct(string $imagePath)
    {
        $this->imagePath = $imagePath;
    }

    public function getPath(): string
    {
        return $this->imagePath;
    }
}
