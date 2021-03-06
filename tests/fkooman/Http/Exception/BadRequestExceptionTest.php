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

class BadRequestExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testBadRequestException()
    {
        $e = new BadRequestException('foo');
        $this->assertSame(400, $e->getCode());
        $this->assertSame('foo', $e->getMessage());
        $this->assertSame(
            [
                'HTTP/1.1 400 Bad Request',
                'Content-Type: application/json',
                'Content-Length: 15',
                '',
                '{"error":"foo"}',
            ],
            $e->getJsonResponse()->toArray()
        );

        $this->assertSame(
            [
                'HTTP/1.1 400 Bad Request',
                'Content-Type: text/html;charset=UTF-8',
                'Content-Length: 136',
                '',
                '<!DOCTYPE HTML><html><head><meta charset="utf-8"><title>400 Bad Request</title></head><body><h1>Bad Request</h1><p>foo</p></body></html>',

            ],
            $e->getHtmlResponse()->toArray()
        );
    }
}
