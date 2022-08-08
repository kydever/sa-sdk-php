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

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use KY\SA\Exception\SensorsAnalyticsDebugException;
use KY\SA\Http;
use KY\SA\Packer;

class DebugConsumer implements ConsumerInterface
{
    /**
     * 用于调试模式.
     * @see http://www.sensorsdata.cn/manual/debug_mode.html
     *
     * @param string $baseUri 服务器的URL地址
     * @param bool $isDryRun 是否只校验数据（是否不实际写入数据）
     * @param int $timeout 请求服务器的超时时间,单位毫秒
     * @throws SensorsAnalyticsDebugException
     */
    public function __construct(private string $baseUri, private bool $isDryRun = false, private int $timeout = 1000)
    {
        if (! $baseUri) {
            throw new SensorsAnalyticsDebugException('The baseUri cannot be empty.');
        }

        $this->baseUri = (string) (new Uri($this->baseUri))->withPath('/debug');
    }

    public function send(string $msg): bool
    {
        $client = Http::create(timeout: $this->timeout);
        $options = [
            RequestOptions::FORM_PARAMS => [
                'data_list' => Packer::pack([$msg]),
                'gzip' => 1,
            ],
            RequestOptions::VERIFY => false,
        ];
        if ($this->isDryRun) {
            $options[RequestOptions::HEADERS] = [
                'Dry-Run' => 'true',
            ];
        }
        $response = $client->post($this->baseUri, $options);
        if ($response->getStatusCode() !== 200) {
            throw new SensorsAnalyticsDebugException((string) $response->getBody(), $response->getStatusCode());
        }

        return true;
    }

    public function flush(): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }
}
