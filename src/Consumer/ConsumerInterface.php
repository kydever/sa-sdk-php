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
namespace KY\SA\Consumer;

interface ConsumerInterface
{
    /**
     * 发送一条消息。
     *
     * @param string $msg 发送的消息体
     */
    public function send(string $msg): bool;

    /**
     * 立即发送所有未发出的数据。
     */
    public function flush(): bool;

    /**
     * 关闭 Consumer 并释放资源。
     */
    public function close(): bool;
}
