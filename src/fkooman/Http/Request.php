<?php

/**
 * Copyright 2015 François Kooman <fkooman@tuxed.net>.
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

class Request
{
    /** @var array */
    private $srv;

    /** @var array */
    private $post;

    /** @var fkooman\Http\Url */
    private $url;

    /**
     * Contruct the Request object.
     *
     * @param array $srv  the server parameters, typically $_SERVER
     * @param array $post the server POST parameters, typically $_POST
     */
    public function __construct(array $srv = null, array $post = null)
    {
        if (null === $srv) {
            $srv = $_SERVER;
        }
        if (null === $post) {
            $post = $_POST;
        }

        $requiredKeys = array('REQUEST_METHOD');
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $srv)) {
                throw new RuntimeException(sprintf('missing key "%s"', $key));
            }
        }
        $this->srv = $srv;
        $this->post = $post;
        $this->url = new Url($srv);
    }

    /**
     * Get the Url object.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get the POST parameters.
     *
     * @return array the key value pair POST parameters
     */
    public function getPostParameters()
    {
        return $this->post;
    }

    /**
     * Get a specific POST parameter.
     *
     * @param string $key the POST key parameter to retrieve.
     *
     * @return string|null the value of the POST parameter key, or null if the
     *                     key does not exist
     */
    public function getPostParameter($key)
    {
        if (array_key_exists($key, $this->post)) {
            return $this->post[$key];
        }

        return;
    }

    /**
     * Set the HTTP method manually. Used for HTTP method override from
     * Service class to support _METHOD form override.
     *
     * @param string $method the HTTP method to switch to
     */
    public function setMethod($method)
    {
        $this->srv['REQUEST_METHOD'] = $method;
    }

    /**
     * Get the HTTP request method.
     *
     * @return string the HTTP method
     */
    public function getMethod()
    {
        return $this->srv['REQUEST_METHOD'];
    }

    /**
     * Get the request header.
     *
     * @param string $k the HTTP header to retrieve
     *
     * @return string|null the header value or null if the header key is not
     *                     set
     */
    public function getHeader($k)
    {
        $headers = $this->getHeaders();
        if (0 === strpos($k, 'HTTP_') || 0 === strpos($k, 'HTTP-')) {
            $k = substr($k, 5);
            $k = str_replace(' ', '-', ucwords(strtolower(str_replace(array('_', '-'), ' ', $k))));
        }
        if (array_key_exists($k, $headers)) {
            return $headers[$k];
        }

        return;
    }

    /**
     * Get the HTTP headers.
     *
     * @return array the HTTP headers as a key value array
     */
    public function getHeaders()
    {
        // *** FALLBACK for FastCGI ***
        // Source: https://php.net/manual/en/function.getallheaders.php#104307
        // Get all headers prefixed with HTTP{_-} and also Content-Type and
        // Content-Length from $_SERVER if available

        $headers = array();
        foreach ($this->srv as $k => $v) {
            if (0 === strpos($k, 'HTTP_') || 0 === strpos($k, 'HTTP-')) {
                $k = str_replace(' ', '-', ucwords(strtolower(str_replace(array('_', '-'), ' ', substr($k, 5)))));
                $headers[$k] = $v;
                continue;
            }
            if ('CONTENT_TYPE' === $k) {
                $headers['Content-Type'] = $v;
            }
            if ('CONTENT_LENGTH' === $k) {
                $headers['Content-Length'] = $v;
            }
            $headers[$k] = $v;
        }

        return $headers;
    }

    /**
     * Get the HTTP request body.
     *
     * @return string the HTTP body as a string, can also be binary.
     */
    public function getBody()
    {
        return @file_get_contents('php://input');
    }
}
