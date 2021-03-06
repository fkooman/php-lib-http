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

use RuntimeException;

class JsonResponse extends Response
{
    public function __construct($statusCode = 200)
    {
        parent::__construct($statusCode, 'application/json');
    }

    public function getBody()
    {
        $decodedJson = @json_decode(parent::getBody(), true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new RuntimeException('error decoding JSON');
        }

        return $decodedJson;
    }

    public function setBody($body)
    {
        $encodedJson = @json_encode($body);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new RuntimeException('error encoding JSON');
        }

        parent::setBody($encodedJson);
    }
}
