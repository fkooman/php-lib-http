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
namespace fkooman\Http;

use PHPUnit_Framework_TestCase;

class JsonResponseTest extends PHPUnit_Framework_TestCase
{
    public function testResponse()
    {
        $h = new JsonResponse(200);
        $h->setBody(['foo' => 'bar']);
        $this->assertSame(
            ['foo' => 'bar'],
            $h->getBody()
        );
        $this->assertSame(
            [
                'HTTP/1.1 200 OK',
                'Content-Type: application/json',
                'Content-Length: 13',
                '',
                '{"foo":"bar"}',
            ],
            $h->toArray()
        );
    }
}
