[![Build Status](https://travis-ci.org/fkooman/php-lib-rest.png?branch=master)](https://travis-ci.org/fkooman/php-lib-rest)

# Introduction
Library written in PHP to make it easy to develop web and REST applications. 

# Features
The library has the following features:
* Wrapper HTTP `Request` and `Response` class to make it very easy to test your
  applications
* RESTful router support
* Various plugins for authentication

Furthermore, extensive tests are available written in PHPUnit.

# Installation
You can use this library through [Composer](http://getcomposer.org/) by 
requiring `fkooman/rest`.

# Tests
You can run the PHPUnit tests if PHPUnit is installed:

    $ phpunit

You need to run Composer **FIRST** in order to be able to run the tests:

    $ php /path/to/composer.phar install
        
# Example
A simple sample application can be found in the `examples/` directory. 
Please check there to see how to use this library. The example should work
"as is" when placed in a directory reachable through a web server.

# License
Licensed under the Apache License, Version 2.0;

   http://www.apache.org/licenses/LICENSE-2.0
