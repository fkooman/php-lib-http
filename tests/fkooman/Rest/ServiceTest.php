<?php

/**
* Copyright 2015 François Kooman <fkooman@tuxed.net>
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

namespace fkooman\Rest;

use fkooman\Http\Request;
use fkooman\Http\Response;
use StdClass;
use PHPUnit_Framework_TestCase;

class ServiceTest extends PHPUnit_Framework_TestCase
{
    public function testSimpleMatch()
    {
        $request = new Request("http://www.example.org/foo", "GET");
        $request->setPathInfo("/foo/bar/baz.txt");

        $service = new Service();
        $service->get(
            "/foo/bar/baz.txt",
            function () {
                $response = new Response(200, "text/plain");
                $response->setContent("Hello World");

                return $response;
            }
        );
        $response = $service->run($request);
        $this->assertEquals("Hello World", $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testOnMatchPluginNoSkip()
    {
        $service = new Service();

        $stub = $this->getMock('fkooman\Rest\ServicePluginInterface');
        $stub->method('execute')
             ->willReturn((object) array("foo" => "bar"));

        $service->registerOnMatchPlugin($stub);
        $service->get(
            "/foo/bar/baz.txt",
            function (StdClass $x) {
                $response = new Response(200, "text/plain");
                $response->setContent($x->foo);

                return $response;
            }
        );
        $service->get(
            "/foo/bar/bazzz.txt",
            function (StdClass $x) {
                $response = new Response(200, "text/plain");
                $response->setContent($x->foo);

                return $response;
            }
        );
        $request = new Request("http://www.example.org/foo", "GET");
        $request->setPathInfo("/foo/bar/baz.txt");
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("bar", $response->getContent());

        $request->setPathInfo("/foo/bar/bazzz.txt");
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("bar", $response->getContent());
    }

    /**
     * @expectedException BadFunctionCallException
     * @expectedExceptionMessage parameter expected by callback not available
     */
    public function testOnMatchPluginSkip()
    {
        $service = new Service();

        $stub = $this->getMockBuilder('fkooman\Rest\ServicePluginInterface')
                     ->setMockClassName('FooPlugin')
                     ->getMock();
        $stub->method('execute')
             ->willReturn((object) array("foo" => "bar"));
        $service->registerOnMatchPlugin($stub);

        $service->get(
            "/foo/bar/foobar.txt",
            function (StdClass $x) {
            }
        );

        // because the plugin is skipped, the StdClass should not be available!
        $service->get(
            "/foo/bar/baz.txt",
            function (StdClass $x) {
                $response = new Response(200, "text/plain");
                $response->setContent($x->foo);

                return $response;
            },
            array('skipPlugins' => array('FooPlugin'))
        );

        $request = new Request("http://www.example.org/foo", "GET");
        $request->setPathInfo("/foo/bar/baz.txt");
        $service->run($request);
    }

    /**
     * @expectedException fkooman\Http\Exception\MethodNotAllowedException
     * @expectedExceptionMessage unsupported method
     */
    public function testNonMethodMatch()
    {
        $request = new Request("http://www.example.org/foo", "GET");
        $request->setPathInfo("/foo/bar/baz.txt");

        $service = new Service();
        $service->post("/foo/bar/baz.txt", null);
        $service->delete("/foo/bar/baz.txt", null);
        $service->run($request);
    }

    /**
     * @expectedException fkooman\Http\Exception\NotFoundException
     * @expectedExceptionMessage url not found
     */
    public function testNonPatternMatch()
    {
        $request = new Request("http://www.example.org/foo", "GET");
        $request->setPathInfo("/bar/foo.txt");
        $request->setHeaders(array('Accept' => 'text/html,foo/bar'));

        $service = new Service();
        $service->match("GET", "/foo/:xyz", null);
        $service->run($request);
    }

    public function testNonResponseReturn()
    {
        $request = new Request("http://www.example.org/foo", "GET");
        $request->setPathInfo("/foo/bar/baz.txt");

        $service = new Service();
        $service->match(
            "GET",
            "/foo/bar/baz.txt",
            function () {
                return "Hello World";
            }
        );
        $response = $service->run($request);
        $this->assertEquals("text/html", $response->getContentType());
        $this->assertEquals("Hello World", $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testMatchRest()
    {
        $request = new Request("http://www.example.org/api.php", "GET");
        $request->setPathInfo("/foo/bar/baz");

        $service = new Service();
        $service->match(
            "GET",
            "/:one/:two/:three",
            function ($one, $two, $three) {
                return json_encode(array($one, $two, $three));
            }
        );
        $response = $service->run($request);
        $this->assertEquals('["foo","bar","baz"]', $response->getContent());
    }

    public function testMatchRestNoReplacement()
    {
        $request = new Request("http://www.example.org/api.php", "POST");
        $request->setHeaders(array('Content-Type' => 'application/x-www-form-urlencoded'));
        $request->setPathInfo("/foo/bar/baz");
        $request->setHeaders(array('HTTP_REFERER' => 'http://www.example.org/'));
        $service = new Service();
        $service->match(
            "POST",
            "/foo/bar/baz",
            function () {
                return "match";
            }
        );
        $response = $service->run($request);
        $this->assertEquals("match", $response->getContent());
    }

    /**
     * @expectedException fkooman\Http\Exception\MethodNotAllowedException
     * @expectedExceptionMessage unsupported method
     */
    public function testMatchRestWrongMethod()
    {
        $request = new Request("http://www.example.org/api.php", "POST");
        $request->setHeaders(array('Content-Type' => 'application/x-www-form-urlencoded'));
        $request->setPathInfo("/");
        $service = new Service();
        $service->match(
            "GET",
            "/:one/:two/:three",
            null
        );
        $service->run($request);
    }

    /**
     * @expectedException fkooman\Http\Exception\NotFoundException
     * @expectedExceptionMessage url not found
     */
    public function testMatchRestNoMatch()
    {
        $request = new Request("http://www.example.org/api.php", "GET");
        $request->setPathInfo("/foo/bar/baz/foobar");
        $service = new Service();
        $service->match(
            "GET",
            "/:one/:two/:three",
            null
        );
        $service->run($request);
    }

    /**
     * @expectedException fkooman\Http\Exception\NotFoundException
     * @expectedExceptionMessage url not found
     */
    public function testMatchRestMatchWildcardToShort()
    {
        $request = new Request("http://www.example.org/api.php", "GET");
        $request->setPathInfo("/foo/bar/");
        $service = new Service();
        $service->match(
            "GET",
            "/:one/:two/:three+",
            null
        );
        $service->run($request);
    }

    public function testMatchRestMatchWildcard()
    {
        $request = new Request("http://www.example.org/api.php", "GET");
        $request->setPathInfo("/foo/bar/baz/foobar");
        $service = new Service();
        $service->match(
            "GET",
            "/:one/:two/:three+",
            function ($one, $two, $three) {
                return json_encode(array($one, $two, $three));
            }
        );
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('["foo","bar","baz\/foobar"]', $response->getContent());
    }

    public function testMatchRestMatchWildcardSomewhere()
    {
        $request = new Request("http://www.example.org/api.php", "GET");
        $request->setPathInfo("/foo/bar/baz/foobar");
        $service = new Service();
        $service->match(
            "GET",
            "/:one/:two+/foobar",
            function ($one, $two) {
                return json_encode(array($one, $two));
            }
        );
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('["foo","bar\/baz"]', $response->getContent());
    }

    /**
     * @expectedException fkooman\Http\Exception\NotFoundException
     * @expectedExceptionMessage url not found
     */
    public function testMatchRestWrongWildcard()
    {
        $request = new Request("http://www.example.org/api.php", "GET");
        $request->setPathInfo("/foo/bar/baz/foobar");
        $service = new Service();
        $service->match(
            "GET",
            "/:abc+/foobaz",
            null
        );
        $service->run($request);
    }

    public function testEndingSlashWildcard()
    {
        $request = new Request("http://www.example.org/api.php", "GET");
        $request->setPathInfo("/admin/public/calendar/42/16/");
        $service = new Service();
        $service->get(
            '/:userId/public/:moduleName/:path+/',
            function ($userId, $moduleName, $path, $matchAll) {
                return $matchAll;
            }
        );
        $response = $service->run($request);
        $this->assertEquals('/admin/public/calendar/42/16/', $response->getContent());
    }

    public function testMatchRestMatchWildcardInMiddle()
    {
        $request = new Request("http://www.example.org/api.php", "GET");
        $request->setPathInfo("/foo/bar/baz/foobar");
        $service = new Service();
        $service->match(
            "GET",
            "/:one/:two+/:three",
            function ($one, $two, $three) {
                return json_encode(array($one, $two, $three));
            }
        );
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('["foo","bar\/baz","foobar"]', $response->getContent());
    }

    /**
     * @expectedException fkooman\Http\Exception\NotFoundException
     * @expectedExceptionMessage url not found
     */
    public function testMatchRestNoAbsPath()
    {
        $request = new Request("http://www.example.org/api.php", "GET");
        $request->setPathInfo("foo");
        $service = new Service();
        $service->match(
            "GET",
            "foo",
            null
        );
        $service->run($request);
    }

    /**
     * @expectedException fkooman\Http\Exception\NotFoundException
     * @expectedExceptionMessage url not found
     */
    public function testMatchRestEmptyPath()
    {
        $request = new Request("http://www.example.org/api.php", "GET");
        $request->setPathInfo("");
        $service = new Service();
        $service->match(
            "GET",
            "",
            null
        );
        $service->run($request);
    }

    /**
     * @expectedException fkooman\Http\Exception\NotFoundException
     * @expectedExceptionMessage url not found
     */
    public function testMatchRestNoPatternPath()
    {
        $request = new Request("http://www.example.org/api.php", "GET");
        $request->setPathInfo("/foo");
        $service = new Service();
        $service->match(
            "GET",
            "x",
            null
        );
        $service->run($request);
    }

    /**
     * @expectedException fkooman\Http\Exception\NotFoundException
     * @expectedExceptionMessage url not found
     */
    public function testMatchRestNoMatchWithoutReplacement()
    {
        $request = new Request("http://www.example.org/api.php", "GET");
        $request->setPathInfo("/foo");
        $service = new Service();
        $service->match(
            "GET",
            "/bar",
            null
        );
        $service->run($request);
    }

    /**
     * @expectedException fkooman\Http\Exception\NotFoundException
     * @expectedExceptionMessage url not found
     */
    public function testMatchRestNoMatchWithoutReplacementLong()
    {
        $request = new Request("http://www.example.org/api.php", "GET");
        $request->setPathInfo("/foo/bar/foo/bar/baz");
        $service = new Service();
        $service->match(
            "GET",
            "/foo/bar/foo/bar/bar",
            null
        );
        $service->run($request);
    }

    /**
     * @expectedException fkooman\Http\Exception\NotFoundException
     * @expectedExceptionMessage url not found
     */
    public function testMatchRestTooShortRequest()
    {
        $request = new Request("http://www.example.org/api.php", "GET");
        $request->setPathInfo("/foo");
        $service = new Service();
        $service->match(
            "GET",
            "/foo/bar/:foo/bar/bar",
            null
        );
        $service->run($request);
    }

    /**
     * @expectedException fkooman\Http\Exception\NotFoundException
     * @expectedExceptionMessage url not found
     */
    public function testMatchRestEmptyResource()
    {
        $request = new Request("http://www.example.org/api.php", "GET");
        $request->setPathInfo("/foo/");
        $service = new Service();
        $service->get(
            "/foo/:bar",
            null
        );
        $service->post(
            "/foo/:bar",
            null
        );
        $service->put(
            "/foo/:bar",
            null
        );
        $service->run($request);
    }

    public function testMatchRestVootGroups()
    {
        $request = new Request("http://localhost/oauth/php-voot-proxy/voot.php", "GET");
        $request->setPathInfo("/groups/@me");
        $service = new Service();
        $service->match(
            "GET",
            "/groups/@me",
            function () {
                return "match";
            }
        );
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("match", $response->getContent());
    }

    public function testMatchRestVootPeople()
    {
        $request = new Request("http://localhost/oauth/php-voot-proxy/voot.php", "GET");
        $request->setPathInfo("/people/@me/urn:groups:demo:member");
        $service = new Service();
        $service->match(
            "GET",
            "/people/@me/:groupId",
            function ($groupId) {
                return $groupId;
            }
        );
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("urn:groups:demo:member", $response->getContent());
    }

    public function testMatchRestAllPaths()
    {
        $request = new Request("http://www.example.org/api.php", "OPTIONS");
        $request->setPathInfo("/foo/bar/baz/foobar");
        $service = new Service();
        $service->options(
            "*",
            function () {
                return "match";
            }
        );
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("match", $response->getContent());
    }

    public function testOptionalMatch()
    {
        $request = new Request("http://localhost/php-remoteStorage/api.php", "GET");
        $request->setPathInfo("/admin/public/money/");
        $service = new Service();
        $service->get(
            "/:user/public/:module(/:path+)/",
            function ($user, $module, $path = null) {
                return json_encode(array($user, $module, $path));
            }
        );
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('["admin","money",null]', $response->getContent());
    }

    public function testOtherOptionalMatch()
    {
        $request = new Request("http://localhost/php-remoteStorage/api.php", "GET");
        $request->setPathInfo("/admin/public/money/a/b/c/");
        $service = new Service();
        $service->match(
            "GET",
            "/:user/public/:module(/:path+)/",
            function ($user, $module, $path = null) {
                return json_encode(array($user, $module, $path));
            }
        );
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('["admin","money","a\/b\/c"]', $response->getContent());
    }

    /**
     * @expectedException fkooman\Http\Exception\NotFoundException
     * @expectedExceptionMessage url not found
     */
    public function testWildcardShouldNotMatchDir()
    {
        $request = new Request("http://localhost/php-remoteStorage/api.php", "GET");
        $request->setPathInfo("/admin/money/a/b/c/");
        $service = new Service();
        $service->match(
            "GET",
            "/:user/:module/:path+",
            null
        );
        $service->run($request);
    }

    public function testWildcardShouldMatchDir()
    {
        $request = new Request("http://localhost/php-remoteStorage/api.php", "GET");
        $request->setPathInfo("/admin/money/a/b/c/");
        $service = new Service();
        $service->match(
            "GET",
            "/:user/:module/:path+/",
            function ($user, $module, $path) {
                return json_encode(array($user, $module, $path));
            }
        );
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('["admin","money","a\/b\/c"]', $response->getContent());
    }

    public function testMatchAllWithParameter()
    {
        $request = new Request("http://localhost/php-remoteStorage/api.php", "GET");
        $request->setPathInfo("/admin/money/a/b/c/");
        $service = new Service();
        $service->match(
            "GET",
            "*",
            function ($matchAll) {
                return $matchAll;
            }
        );
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('/admin/money/a/b/c/', $response->getContent());
    }

    public function testMatchAllWithStarParameter()
    {
        $request = new Request("http://localhost/php-remoteStorage/api.php", "DELETE");
        $request->setHeaders(array('HTTP_REFERER' => 'http://localhost/'));
        $request->setPathInfo("/admin/money/a/b/c/");
        $service = new Service();
        $service->delete(
            "*",
            function ($matchAll) {
                return $matchAll;
            }
        );
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('/admin/money/a/b/c/', $response->getContent());
    }

    public function testHeadRequest()
    {
        $request = new Request("http://localhost/php-remoteStorage/api.php", "HEAD");
        $request->setPathInfo("/admin/money/a/b/c/");
        $service = new Service();
        $service->head(
            "*",
            function ($matchAll) {
                return "";
            }
        );
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame(0, strlen($response->getContent()));
    }

    public function testMultipleMethodMatchGet()
    {
        $request = new Request("http://localhost/php-remoteStorage/api.php", "GET");
        $request->setPathInfo("/admin/money/a/b/c/");
        $service = new Service();
        $service->match(
            array(
                "GET",
                "HEAD",
            ),
            "*",
            function ($matchAll) use ($request) {
                return "HEAD" === $request->getRequestMethod() ? "" : $matchAll;
            }
        );
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('/admin/money/a/b/c/', $response->getContent());
    }

    public function testMultipleMethodMatchHead()
    {
        $request = new Request("http://localhost/php-remoteStorage/api.php", "HEAD");
        $request->setPathInfo("/admin/money/a/b/c/");
        $service = new Service();
        $service->match(
            array(
                "GET",
                "HEAD",
            ),
            "*",
            function ($matchAll) use ($request) {
                return "HEAD" === $request->getRequestMethod() ? "" : $matchAll;
            }
        );
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", $response->getContent());
    }

    public function testCallbackRequestParameter()
    {
        $service = new Service();
        $t = &$this;    // needed for PHP 5.3, together with the 'use ($t) below'
        $service->get('/:foo', function (Request $r, $foo) use ($t) {
            // $t is needed for PHP 5.3, in PHP >5.3 you can just use $this
            $t->assertEquals('GET', $r->getRequestMethod());
            $t->assertEquals('xyz', $foo);

            return 'foo';
        });
        $request = new Request('http://www.example.org', 'GET');
        $request->setPathInfo('/xyz');
        $service->run($request);
    }

    public function testNonMatchAllParameterWithWildcard()
    {
        $service = new Service();
        $service->get(
            "*",
            function ($matchAll) {
                return "foobar";
            }
        );
        $request = new Request("http://example.org", "GET");
        $request->setPathInfo("/foo/bar/baz");
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("foobar", $response->getContent());
    }

    public function testMatchRequestParameterOrder()
    {
        $service = new Service();
        $service->get(
            "/:foo/:bar/baz",
            function ($bar, $foo, Request $request) {
                return $foo.$bar.$request->getRequestMethod();
            }
        );
        $request = new Request("http://example.org", "GET");
        $request->setPathInfo("/xxx/yyy/baz");
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("xxxyyyGET", $response->getContent());
    }

    public function testMatchRequestParameterMatchAll()
    {
        $service = new Service();
        $service->get(
            "*",
            function ($matchAll, Request $request) {
                return $matchAll.$request->getRequestMethod();
            }
        );
        $request = new Request("http://example.org", "GET");
        $request->setPathInfo("/xxx/yyy/baz");
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("/xxx/yyy/bazGET", $response->getContent());
    }

    public function testMatchRequestParameterMatchExactNoVariablesRequest()
    {
        $service = new Service();
        $service->get(
            "/foo/bar/baz",
            function (Request $request) {
                return $request->getRequestMethod();
            }
        );
        $request = new Request("http://example.org", "GET");
        $request->setPathInfo("/foo/bar/baz");
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("GET", $response->getContent());
    }

    public function testFormMethodOverrideDelete()
    {
        $service = new Service();
        $service->delete(
            "/foo/bar/baz",
            function (Request $request) {
                return "hello, delete!";
            }
        );
        $request = new Request("http://example.org", "POST");
        $request->setPathInfo("/foo/bar/baz");
        $request->setHeaders(array('HTTP_REFERER' => 'http://example.org/', 'Content-Type' => 'application/x-www-form-urlencoded'));
        $request->setPostParameters(array("_METHOD" => "DELETE"));
        $response = $service->run($request);
        $this->assertEquals("hello, delete!", $response->getContent());
    }

    public function testDefaultRouteOnRoot()
    {
        $service = new Service();
        $service->setDefaultRoute('/welcome');
        $service->get(
            '/welcome',
            function () {
                return 'welcome';
            }
        );
        $request = new Request("http://www.example.org/index.php/", "GET");
        $request->setPathInfo('/');
        $response = $service->run($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals("http://www.example.org/index.php/welcome", $response->getHeader('Location'));
    }

    public function testDefaultRoute()
    {
        $service = new Service();
        $service->setDefaultRoute('/manage/');
        $service->get(
            '/manage/',
            function () {
                return "default_route_works";
            }
        );
        $request = new Request("http://www.example.org/index.php", "GET");
        $response = $service->run($request);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals("http://www.example.org/index.php/manage/", $response->getHeader('Location'));
        $request = new Request("http://www.example.org/index.php", "GET");
        $request->setPathInfo('/manage/');
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('default_route_works', $response->getContent());
    }

    /**
     * @expectedException fkooman\Http\Exception\NotFoundException
     * @expectedExceptionMessage url not found
     */
    public function testNoPathInfo()
    {
        $service = new Service();
        $service->get(
            '/foo',
            function () {
                return "foo";
            }
        );
        $request = new Request("http://www.example.org/index.php", "GET");
        $service->run($request);
    }

    public function testUrlEncodedIndex()
    {
        $service = new Service();
        $service->get(
            '/info/:url',
            function ($url) {
                return $url;
            }
        );
        $request = new Request('http://www.example.org/info/?_index=https://www.example.org/foo/bar/baz');
        $request->setPathInfo('/info/');
        $response = $service->run($request);
        $this->assertEquals('https%3A%2F%2Fwww.example.org%2Ffoo%2Fbar%2Fbaz', $response->getContent());
    }

    /**
     * @expectedException BadFunctionCallException
     * @expectedExceptionMessage parameter expected by callback not available
     */
    public function testDefaultDisablePlugins()
    {
        $service = new Service();

        $stub = $this->getMockBuilder('fkooman\Rest\ServicePluginInterface')
                     ->setMockClassName('FooPlugin')
                     ->getMock();
        $stub->method('execute')
             ->willReturn((object) array("foo" => "bar"));
        $service->registerOnMatchPlugin($stub, array('defaultDisable' => true));

        // because the plugin is skipped by default, the StdClass should not be available!
        $service->get(
            "/foo/bar/baz.txt",
            function (StdClass $x) {
                return $x->foo;
            }
        );
        $request = new Request("http://www.example.org/foo", "GET");
        $request->setPathInfo("/foo/bar/baz.txt");
        $service->run($request);
    }

    public function testDefaultDisablePluginsEnableForRoute()
    {
        $service = new Service();

        $stub = $this->getMockBuilder('fkooman\Rest\ServicePluginInterface')
                     ->setMockClassName('FooPlugin')
                     ->getMock();
        $stub->method('execute')
             ->willReturn((object) array("foo" => "bar"));
        $service->registerOnMatchPlugin($stub, array('defaultDisable' => true));

        // because the plugin is skipped by default, the StdClass should not be available!
        $service->get(
            "/foo/bar/baz.txt",
            function (StdClass $x) {
                return $x->foo;
            },
            array(
                'enablePlugins' => array(
                    'FooPlugin'
                )
            )
        );
        $request = new Request("http://www.example.org/foo", "GET");
        $request->setPathInfo("/foo/bar/baz.txt");
        $response = $service->run($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('bar', $response->getContent());
    }

    /**
     * @expectedException fkooman\Http\Exception\BadRequestException
     * @expectedExceptionMessage CSRF protection triggered
     */
    public function testReferrerCheck()
    {
        $service = new Service();
        $service->setReferrerCheck(true);
        $service->post(
            '/foo',
            function (Request $request) {
                return 'foo';
            }
        );

        $request = new Request('http://example.org/foo', 'POST');
        $request->setPathInfo('/foo');
        $service->run($request);
    }

    public function testReferrerCheckDisabled()
    {
        $service = new Service();
        $service->setReferrerCheck(true);
        $service->post(
            '/foo',
            function (Request $request) {
                return 'foo';
            },
            array('disableReferrerCheck' => true)
        );

        $request = new Request('http://example.org/foo', 'POST');
        $request->setPathInfo('/foo');
        $response = $service->run($request);
        $this->assertEquals('foo', $response->getContent());
    }
}
