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
namespace HyperfTest\Cases\Consumer;

use Hyperf\Utils\Reflection\ClassInvoker;
use HyperfTest\Cases\AbstractTestCase;
use KY\SA\Consumer\DebugConsumer;

/**
 * @internal
 * @coversNothing
 */
class DebugConsumerTest extends AbstractTestCase
{
    public function testConstruct()
    {
        $consumer = new DebugConsumer(
            'https://sc.shence.com/sa?project=test'
        );

        $consumer = new ClassInvoker($consumer);
        $baseUri = $consumer->baseUri;

        $this->assertSame('https://sc.shence.com/debug?project=test', $baseUri);
    }
}
