<?php

declare(strict_types=1);
/**
 * This file is part of KnowYourself.
 *
 * @link     https://www.knowyourself.cc
 * @document https://github.com/kydever/sa-sdk-php
 * @contact  l@hyperf.io
 * @license  https://github.com/kydever/sa-sdk-php/blob/main/LICENSE
 */
namespace KY\SA;

use KY\SA\Consumer\ConsumerInterface;
use KY\SA\Event\Event;
use KY\SA\Event\TrackEvent;

class SensorsAnalytics implements SensorsAnalyticsInterface
{
    protected array $defaultProperties = [
        '$lib' => 'php',
        '$lib_version' => Constant::VERSION,
    ];

    protected bool $isWindows = false;

    public function __construct(protected string $project, protected ConsumerInterface $consumer)
    {
        if (str_starts_with(strtoupper(PHP_OS), 'WIN')) {
            $this->isWindows = true;
        }
    }

    /**
     * 跟踪一个用户的行为。
     */
    public function track(TrackEvent $event): bool
    {
        $event->properties = array_merge($this->defaultProperties, $event->properties);

        return $this->trackEvent($event);
    }

    protected function trackEvent(Event $event): bool
    {
        return $this->consumer->send(Json::encode($event->build($this->project, $this->isWindows)));
    }
}
