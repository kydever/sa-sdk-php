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

use DateTime;
use KY\SA\Constant;
use KY\SA\Exception\SensorsAnalyticsIllegalDataException;

class Event implements \JsonSerializable
{
    public array $data = [];

    public function __construct(
        public string $updateType,
        public string $name,
        public string $distinctId,
        public bool $isLoginId,
        public ?string $originalId = null,
        public array $properties = [],
        public array $identities = [],
        public array $libProperties = [],
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }

    public function build(string $project, bool $isWindows = false): static
    {
        if (! $this->distinctId || strlen($this->distinctId) > 255) {
            throw new SensorsAnalyticsIllegalDataException('distinct_id is invalid.');
        }

        $this->assertKeyWithRegex($this->name);
        $this->assertProperties();
        $this->assertIdentities();

        $eventTime = $this->getEventTime($isWindows);

        if ($this->isLoginId) {
            $this->properties['$is_login_id'] = true;
        }

        $data = [
            'type' => $this->updateType,
            'properties' => $this->properties,
            'time' => $eventTime,
            'distinct_id' => $this->distinctId,
            'lib' => $this->getLibProperties(),
        ];

        if ($project) {
            $data['project'] = $project;
        }

        if (strcmp($this->updateType, 'track') == 0 || strcmp($this->updateType, 'track_id_bind') == 0 || strcmp($this->updateType, 'track_id_unbind') == 0) {
            $data['event'] = $this->name;
        } elseif (strcmp($this->updateType, 'track_signup') == 0) {
            $data['event'] = $this->name;
            $data['original_id'] = $this->originalId;
        }

        if ($this->identities) {
            $data['identities'] = $this->identities;
        }

        $this->data = $data;
        return $this;
    }

    public function getEventTime(bool $isWindows = false): int|string
    {
        $time = microtime(true) * 1000;
        if (array_key_exists('$time', $this->properties)) {
            $time = $this->properties['$time'];
            unset($this->properties['$time']);
        }
        if ($isWindows) {
            return (string) $time;
        }
        return (int) $time;
    }

    /**
     * 返回埋点管理相关属性，由于该函数依赖函数栈信息，因此修改调用关系时，一定要谨慎.
     */
    private function getLibProperties()
    {
        $result = [
            '$lib' => 'php',
            '$lib_version' => Constant::VERSION,
            '$lib_method' => 'code',
        ];

        if (isset($this->defaultProperties['$app_version'])) {
            $result['$app_version'] = $this->defaultProperties['$app_version'];
        }

        return array_merge($result, $this->libProperties);
    }

    private function assertKeyWithRegex(string $key)
    {
        $pattern = '/^((?!^distinct_id$|^original_id$|^time$|^properties$|^id$|^first_id$|^second_id$|^users$|^events$|^event$|^user_id$|^date$|^datetime$|^user_group|^user_tag)[a-zA-Z_$][a-zA-Z\\d_$]{0,99})$/i';
        if (! preg_match($pattern, $key)) {
            throw new SensorsAnalyticsIllegalDataException("key must be a valid variable key. [key='{$key}']");
        }
    }

    private function assertProperties()
    {
        $pattern = '/^((?!^distinct_id$|^original_id$|^time$|^properties$|^id$|^first_id$|^second_id$|^users$|^events$|^event$|^user_id$|^date$|^datetime$)[a-zA-Z_$][a-zA-Z\\d_$]{0,99})$/i';

        if (! $this->properties) {
            return;
        }

        foreach ($this->properties as $key => $value) {
            if (! is_string($key)) {
                throw new SensorsAnalyticsIllegalDataException("property key must be a str. [key={$key}]");
            }
            if (strlen($key) > 255) {
                throw new SensorsAnalyticsIllegalDataException("the max length of property key is 256. [key={$key}]");
            }

            if (! preg_match($pattern, $key)) {
                throw new SensorsAnalyticsIllegalDataException("property key must be a valid variable name. [key='{$key}']]");
            }

            // 只支持简单类型或数组或DateTime类
            if (! is_scalar($value) && ! is_array($value) && ! $value instanceof DateTime) {
                throw new SensorsAnalyticsIllegalDataException("property value must be a str/int/float/datetime/list. [key='{$key}']");
            }

            // 如果是 DateTime，Format 成字符串
            if ($value instanceof DateTime) {
                $data['properties'][$key] = $value->format('Y-m-d H:i:s.0');
            }

            if (is_string($value) && strlen($value) > 8191) {
                throw new SensorsAnalyticsIllegalDataException("the max length of property value is 8191. [key={$key}]");
            }

            // 如果是数组，只支持 Value 是字符串格式的简单非关联数组
            if (is_array($value)) {
                if (array_values($value) !== $value) {
                    throw new SensorsAnalyticsIllegalDataException("[list] property must not be associative. [key='{$key}']");
                }

                foreach ($value as $lvalue) {
                    if (! is_string($lvalue)) {
                        throw new SensorsAnalyticsIllegalDataException("[list] property's value must be a str. [value='{$lvalue}']");
                    }
                }
            }
        }
    }

    private function assertIdentities()
    {
        foreach ($this->identities as $key => $value) {
            $this->assertKeyWithRegex($key);
            if (! $value || strlen($key) > 255) {
                throw new SensorsAnalyticsIllegalDataException(sprintf('the value %s is too long, max length is 255.', $value));
            }
        }
    }
}
