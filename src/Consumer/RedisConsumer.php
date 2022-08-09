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

use GuzzleHttp\RequestOptions;
use KY\SA\Exception\SensorsAnalyticsDebugException;
use KY\SA\Exception\SensorsAnalyticsException;
use KY\SA\Http;
use KY\SA\Packer;
use Redis;

class RedisConsumer implements ConsumerInterface
{
    /**
     * @var int 投递数据的超时时间
     */
    protected int $timeout = 10;

    /**
     * @var int 最大队列长度
     */
    protected int $maxSize = 50;

    /**
     * @var int 最小队列长度
     */
    protected int $minSize = 2;

    /**
     * @var int 最大投递间隔
     */
    protected int $interval = 60;

    /**
     * @var int 上次投递数据的时间戳
     */
    protected int $timestamp = 0;

    /**
     * @param Redis $redis
     */
    public function __construct(protected string $baseUri, protected mixed $redis, protected string $prefix = '{sa:msg}:')
    {
        $this->timestamp = time();
    }

    public function send(string $msg): bool
    {
        $length = $this->redis->rPush($this->listKey(), $msg);

        if ($length >= $this->maxSize) {
            $this->flush();
        } elseif ($length >= $this->minSize && $this->timestamp + $this->interval < time()) {
            $this->flush();
        }

        return true;
    }

    public function getList(): array
    {
        $length = max($this->maxSize - 1, 5);
        $this->redis->pipeline();
        $this->redis->lRange($this->listKey(), 0, $length - 1);
        $this->redis->lTrim($this->listKey(), $length, -1);
        [$list, $ret] = $this->redis->exec();
        if (! $ret) {
            throw new SensorsAnalyticsException('Remove list from redis failed.');
        }

        return $list;
    }

    public function flush(): bool
    {
        if (! $this->redis->set($this->lockKey(), '1', ['NX', 'EX' => 60])) {
            return false;
        }

        $list = $this->getList();

        // 刷新最后发送时间
        $this->timestamp = time();

        if (! $list) {
            return false;
        }

        try {
            $client = Http::create(timeout: $this->timeout);
            $options = [
                RequestOptions::FORM_PARAMS => [
                    'data_list' => Packer::pack($list),
                    'gzip' => 1,
                ],
                RequestOptions::VERIFY => false,
            ];
            $response = $client->post($this->baseUri, $options);
            if ($response->getStatusCode() !== 200) {
                throw new SensorsAnalyticsDebugException((string) $response->getBody(), $response->getStatusCode());
            }
        } finally {
            $this->redis->del($this->lockKey());
        }

        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function lockKey(): string
    {
        return $this->prefix . 'lock';
    }

    protected function listKey(): string
    {
        return $this->prefix . 'list';
    }
}
