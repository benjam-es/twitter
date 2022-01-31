<?php

declare(strict_types=1);

namespace NotificationChannels\Twitter\Exceptions;

final class CouldNotSendNotification extends \Exception
{
    public static function serviceRespondsNotSuccessful(mixed $response): CouldNotSendNotification
    {
        if (isset($response->error)) {
            return new static("Couldn't post notification. Response: " . $response->error);
        }

        $responseBody = print_r($response->errors[0]->message, true);

        return new static("Couldn't post notification. Response: " . $responseBody);
    }

    public static function userWasNotFound(mixed $response): CouldNotSendNotification
    {
        $responseBody = print_r($response->errors[0]->message, true);

        return new static("Couldn't send direct message notification. Response: " . $responseBody);
    }

    public static function statusUpdateTooLong(int $exceededLength): CouldNotSendNotification
    {
        return new static("Couldn't post notification, because the status message was too long by " . $exceededLength . ' character(s).');
    }
}
