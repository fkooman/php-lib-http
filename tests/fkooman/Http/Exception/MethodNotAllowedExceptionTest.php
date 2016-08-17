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

class MethodNotAllowedExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testMethodNotAllowedException()
    {
        $e = new MethodNotAllowedException('DELETE', array('GET', 'POST'));

        $this->assertSame(
            array(
                'HTTP/1.1 405 Method Not Allowed',
                'Content-Type: application/json',
                'Content-Length: 39',
                'Allow: GET,POST',
                '',
                '{"error":"method DELETE not supported"}',
            ),
            $e->getJsonResponse()->toArray()
        );
    }

    public function testNoMethodAllowed()
    {
        $e = new MethodNotAllowedException('GET', array());

        $this->assertSame(
            array(
                'HTTP/1.1 405 Method Not Allowed',
                'Content-Type: application/json',
                'Content-Length: 36',
                '',
                '{"error":"method GET not supported"}',
            ),
            $e->getJsonResponse()->toArray()
        );
    }
}
