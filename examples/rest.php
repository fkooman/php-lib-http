<?php

/**
* Copyright 2013 François Kooman <fkooman@tuxed.net>
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

require_once dirname(__DIR__).'/vendor/autoload.php';

use fkooman\Http\Request;
use fkooman\Http\JsonResponse;
use fkooman\Http\IncomingRequest;
use fkooman\Rest\Service;
use fkooman\Rest\Plugin\BasicAuthentication;
use fkooman\Http\Exception\HttpException;
use fkooman\Http\Exception\BadRequestException;
use fkooman\Http\Exception\InternalServerErrorException;

try {
    $service = new Service();

#    // require all requests to have valid authentication
#    $u = 'foo';
#    // NOTE: password is generated using the "password_hash()" function from
#    // PHP 5.6 or the ircmaxell/password-compat library. This way no plain
#    // text passwords are stored anywhere, below is the hashed value of 'bar'
#    $p = '$2y$10$ARD9Oq9xCzFANYGhv0mWxOsOallAS3qLQxLoOtzzRuLhv0U1IU9EO';

#    $service->registerBeforeMatchingPlugin(
#       new BasicAuthentication($u, $p, 'My Secured Foo Service')
#    );

    $service->get(
        '/hello/:str',
        function ($str) {
            $response = new JsonResponse();
            $response->setContent(
                array(
                    'type' => 'GET',
                    'response' => sprintf('hello %s', $str),
                )
            );

            return $response;
        }
    );

    $service->post(
        '/hello/:str',
        function ($str) {
            if ('foo' === $str) {
                throw new BadRequestException('you cannot say "foo!"');
            }
            $response = new JsonResponse();
            $response->setContent(
                array(
                    'type' => 'POST',
                    'response' => sprintf('hello %s', $str),
                )
            );

            return $response;
        }
    );

    $request = Request::fromIncomingRequest(
        new IncomingRequest()
    );
    $service->run($request)->sendResponse();
} catch (Exception $e) {
    if ($e instanceof HttpException) {
        $response = $e->getJsonResponse();
    } else {
        // we catch all other (unexpected) exceptions and return a 500
        $e = new InternalServerErrorException($e->getMessage());
        $response = $e->getJsonResponse();
    }
    $response->sendResponse();
}
