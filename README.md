[![Build Status](https://travis-ci.org/fkooman/php-lib-http.png?branch=master)](https://travis-ci.org/fkooman/php-lib-http)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fkooman/php-lib-http/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fkooman/php-lib-http/?branch=master)

# Introduction
Very simple HTTP helper library to deal with incoming HTTP requests and 
outgoing HTTP responses.

# Features
The library handles requests, responses and URLs. 

    use fkooman\Http\Request;

    $r = new Request();
    echo $r->getUrl();

The library includes extensive tests.

# Integration

The following request headers MUST be set by the web server:

- `REQUEST_SCHEME`
- `SERVER_NAME`
- `SERVER_PORT`
- `REQUEST_URI`
- `REQUEST_METHOD`

# Installation
You can use this library through [Composer](http://getcomposer.org/) by 
requiring `fkooman/http`.

# Tests
You can run the PHPUnit tests if PHPUnit is installed:

    $ phpunit

You need to run Composer **FIRST** in order to be able to run the tests:

    $ php /path/to/composer.phar install
        
# License
Licensed under the Apache License, Version 2.0;

   http://www.apache.org/licenses/LICENSE-2.0
