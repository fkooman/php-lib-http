[![Build Status](https://travis-ci.org/fkooman/php-lib-http.png?branch=master)](https://travis-ci.org/fkooman/php-lib-http)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fkooman/php-lib-http/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fkooman/php-lib-http/?branch=master)

# Introduction

Simple HTTP helper library.

# Features

This library makes it easy to:

- handle HTTP requests;
- create and send HTTP responses;
- handle sessions

# API

## Request

Typically you'd use the `Request` class to construct an object based on the 
current request:

    use fkooman\Http\Request;

    // creates the object from $_SERVER, $_POST or php://input
    $r = new Request();
    
    // shows the request method, e.g. GET/POST/DELETE
    echo $r->getMethod();

    // shows the full request URL
    echo $r->getUrl();

    // get POST parameter
    echo $r->getPostParameter('foo');

    // get HTTP header value
    echo $r->getHeader('Accept');

## Response

    use fkooman\Http\Response;

    // create the object with response code and content type
    $r = new Response(200, 'text/plain');

    // set a response header
    $r->setHeader('X-Foo', 'Bar');

    // set the body content
    $r->setBody('Simple Plain Text');

    // send the response
    $r->send();

There is also an `fkooman\Http\JsonResponse` for sending JSON responses, the
`setBody()` method accepts an `array`. 

For sending redirects to the browser `fkooman\Http\RedirectResponse` is 
available:

    use fkooman\Http\RedirectResponse;

    // send a (temporary) redirect
    $r = new RedirectResponse('https://www.example.org/', 302);
    $r->send();

## Session

    use fkooman\Http\Session;

    // create the session
    $s = new Session('My Session');

    // set, get, delete variables
    $s->set('foo', 'bar');
    $s->get('foo');
    $s->delete('foo');

    // check whether a variable is set
    $s->has('foo');

    // destroy a session
    $s->destroy();

# Installation

You can use this library through [Composer](http://getcomposer.org/) by 
requiring `fkooman/http`:

    $ composer require fkooman/http

# Deployment Considerations

## Apache

    ServerName https://server.name:443
    UseCanonicalName on

See https://httpd.apache.org/docs/2.4/mod/core.html for `ServerName`, 
`UseCanonicalName` and `UseCanonicalPhysicalPort`.

Without `UseCanonicalName on` the `SERVER_NAME` variable is set using the 
`HTTP_HOST` header. This is correct in most cases, but if you want to use 
the `SERVER_NAME` as something reliable under your web server's control you 
need to set `UseCanonicalName on`.

# Tests

Extensive tests for PHPUnit are available.

# License

Licensed under the Apache License, Version 2.0;

   http://www.apache.org/licenses/LICENSE-2.0
