<?php

/**
 * Copyright 2016 François Kooman <fkooman@tuxed.net>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace fkooman\Http\Exception;

use PHPUnit_Framework_TestCase;

class HttpExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testHttpException()
    {
        $e = new HttpException('foo', 'foo_description', 404);
        $this->assertSame(404, $e->getCode());
        $this->assertSame('foo', $e->getMessage());
        $this->assertSame('foo_description', $e->getDescription());
    }

    public function testHttpExceptionHtmlMessageEscaping()
    {
        $e = new HttpException('xyz&\'', 'foo_description', 404);
        $this->assertSame(
            [
                'HTTP/1.1 404 Not Found',
                'Content-Type: text/html;charset=UTF-8',
                'Content-Length: 161',
                '',
                '<!DOCTYPE HTML><html><head><meta charset="utf-8"><title>404 Not Found</title></head><body><h1>Not Found</h1><p>xyz&amp;&#039; (foo_description)</p></body></html>',
            ],
            $e->getHtmlResponse()->toArray()
        );
    }
}
