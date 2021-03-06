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

class SessionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // needed to the unit test on CentOS 6
        ini_set('session.save_path', sys_get_temp_dir());
        @session_start();
    }

    public function testSetGetValue()
    {
        $s = new Session();
        $s->set('foo', 'bar');
        $this->assertSame('bar', $s->get('foo'));
        $s->delete('foo');
        $this->assertNull($s->get('foo'));
        $s->destroy();
        $this->assertNull($s->get('foo'));
    }
}
