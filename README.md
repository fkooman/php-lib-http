[![Build Status](https://travis-ci.org/fkooman/php-lib-http.png?branch=master)](https://travis-ci.org/fkooman/php-lib-http)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fkooman/php-lib-http/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fkooman/php-lib-http/?branch=master)

# Introduction
Helper library for `fkooman/rest` to deal with HTTP requests and responses.

# Features
The library has the following features:
* Wrapper HTTP `Request` and `Response` class to make it very easy to test your
  applications

Extensive tests are available written in PHPUnit.

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
