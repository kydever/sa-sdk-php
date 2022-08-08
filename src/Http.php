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

use GuzzleHttp\Client;

class Http
{
    /**
     * @param int $timeout 超时时间 秒
     */
    public static function create(?string $baseUri = null, int $timeout = 2): Client
    {
        return new Client([
            'base_uri' => $baseUri,
            'timeout' => $timeout,
        ]);
    }
}
