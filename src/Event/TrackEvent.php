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
namespace KY\SA\Event;

class TrackEvent extends Event
{
    public function __construct(string $name, string $distinctId, bool $isLoginId, array $properties, array $libProperties = [])
    {
        parent::__construct(
            'track',
            $name,
            $distinctId,
            $isLoginId,
            properties: $properties,
            libProperties: $libProperties
        );
    }
}
