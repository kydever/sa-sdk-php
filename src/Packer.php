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

class Packer
{
    public static function pack(mixed $data): string
    {
        return match (true) {
            is_array($data) => self::packArray($data),
            default => self::packString((string) $data),
        };
    }

    public static function packString(string $data): string
    {
        return base64_encode(gzencode($data));
    }

    public static function packArray(array $list): string
    {
        return self::packString('[' . implode(',', $list) . ']');
    }
}
