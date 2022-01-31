<?php

declare(strict_types=1);

namespace NotificationChannels\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\ServiceProvider;

final class TwitterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->when([TwitterChannel::class, TwitterDirectMessage::class])
            ->needs(TwitterOAuth::class)
            ->give(function () {
                return new TwitterOAuth(
                    config('services.twitter.consumer_key'),
                    config('services.twitter.consumer_secret'),
                    config('services.twitter.access_token'),
                    config('services.twitter.access_secret')
                );
            });
    }
}
