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
    /**
     * @param string $name 事件名称
     * @param array $properties 事件的属性
     * @param string $distinctId 用户的唯一标识
     * @param bool $isLoginId 用户标识是否是登录 ID，false 表示该标识是一个匿名 ID
     */
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
