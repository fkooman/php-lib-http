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

/**
 * Url Class.
 *
 * Helper class to easily determine the request URL. It has a number of
 * important functions:
 *
 * - Determine the REST route (i.e.: PATH_INFO) that should be executed. There
 *   are a number of scenarios we want to support. For example of the requested
 *   route is /foo/bar the request could look like any of these:
 *   - http://www.example.org/app/index.php/foo/bar (in folder 'app'
 *   - http://www.example.org/app/foo/bar (URL rewriting to index.php)
 *   - http://www.example.org/index.php/foo/bar (in the root)
 *   - http://www.example.org/foo/bar (in the root with rewriting)
 *
 *   We want to make it possible to figure out the (relative and absolute) path
 *   of the application, if it runs in the root we want to return '/', if it
 *   runs in a folder we want to return /app/.
 *
 *   In essence, we need to detect whether or not URL rewriting is active and
 *   whether or not the application is located in the root or in a folder.
 *   Various server variables give different results for different web servers
 *   and configurations and also for different PHP versions and whether or not
 *   php-fpm is used. This all needs to be made consisent.
 *
 * - Make the query parameters easily available.
 *
 * - Make it possible to reconstruct the full request URL from the server
 *   variables.
 */
class Url
{
    /** @var string */
    private $scheme;

    /** @var string */
    private $serverName;

    /** @var int */
    private $serverPort;

    /** @var string */
    private $requestUri;

    /** @var string */
    private $pathInfo;

    /**
     * Create the Url object.
     *
     * @param array $srv the server variables, typically $_SERVER
     */
    public function __construct(array $srv)
    {
        $this->scheme = self::extractScheme($srv);

        $requiredKeys = [
            'SERVER_NAME',      // e.g. foo.example.org
            'SERVER_PORT',      // e.g. 443
            'REQUEST_URI',      // e.g. /foo/bar?baz=123
        ];
        foreach ($requiredKeys as $k) {
            if (!array_key_exists($k, $srv)) {
                throw new RuntimeException(sprintf('missing key "%s"', $k));
            }
        }

        $this->pathInfo = self::extractPathInfo($srv);

        $this->serverName = $srv['SERVER_NAME'];
        $this->serverPort = intval($srv['SERVER_PORT']);
        $this->requestUri = $srv['REQUEST_URI'];
    }

    /**
     * Get the URL scheme.
     *
     * @return string the URL scheme
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Get the URL host.
     *
     * @return string the URL host
     */
    public function getHost()
    {
        return $this->serverName;
    }

    /**
     * Get the URL port.
     *
     * @return int the URL port
     */
    public function getPort()
    {
        return $this->serverPort;
    }

    /**
     * Get the REQUEST_URI.
     *
     * @return string the REQUEST_URI
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * Get the PATH_INFO.
     *
     * @return string the PATH_INFO
     */
    public function getPathInfo()
    {
        return $this->pathInfo;
    }

    /**
     * The query string.
     *
     * @return string the query string, or empty string if no query string
     *                is available
     */
    public function getQueryString()
    {
        $requestUri = $this->getRequestUri();

        if (false !== $qPos = mb_strpos($requestUri, '?')) {
            // has query string
            return mb_substr($requestUri, $qPos + 1);
        }

        return '';
    }

    /**
     * The query string as array.
     *
     * @return array the query string as array. The array will be empty if
     *               the query string is empty
     */
    public function getQueryStringAsArray()
    {
        $queryString = $this->getQueryString();

        if ('' === $queryString) {
            return [];
        }

        $queryStringArray = [];
        parse_str($queryString, $queryStringArray);

        return $queryStringArray;
    }

    /**
     * Return a specific query string parameter value.
     *
     * @param string $key the query parameter key to get
     *
     * @return mixed the query parameter value if it is set, or null if the
     *               parameter is not available
     */
    public function getQueryParameter($key)
    {
        $queryStringArray = $this->getQueryStringAsArray();

        if (array_key_exists($key, $queryStringArray)) {
            return $queryStringArray[$key];
        }

        return null;
    }

    /**
     * Get the REQUEST_URI without PATH_INFO and QUERY_STRING, taking server
     * rewriting in consideration.
     *
     * Example (without URL rewriting):
     * https://www.example.org/foo/index.php/bar?a=b will return:
     * '/foo/index.php'
     *
     * Example (with URL rewriting to index.php):
     * https://www.example.org/foo/bar?a=b will return:
     * '/foo'
     *
     * Example (with URL rewriting to index.php without sub folder):
     * https://www.example.org/bar?a=b will return:
     * ''
     */
    public function getRoot()
    {
        $requestUri = $this->getRequestUri();
        $pathInfo = $this->getPathInfo();

        // remove query string from REQUEST_URI, if set
        if (false !== $qPos = mb_strpos($requestUri, '?')) {
            // has query string
            $requestUri = mb_substr($requestUri, 0, $qPos + 1);
        }

        // remove PATH_INFO from REQUEST_URI, if set
        if (false !== $pPos = mb_strrpos($requestUri, $pathInfo)) {
            // has PATH_INFO
            $requestUri = mb_substr($requestUri, 0, $pPos + 1);
        }

        return $requestUri;
    }

    /**
     * Get the root as a full URL.
     */
    public function getRootUrl()
    {
        return $this->getAuthority().$this->getRoot();
    }

    /**
     * Get the authority part of the URL. That is, the scheme, host and
     * optional port if it is not a standard port.
     *
     * @return string the authority part of the URL
     */
    public function getAuthority()
    {
        $scheme = $this->getScheme();
        $serverName = $this->getHost();
        $serverPort = $this->getPort();

        $authority = sprintf('%s://%s', $scheme, $serverName);
        if (('https' === $scheme && 443 !== $serverPort) || ('http' === $scheme && 80 !== $serverPort)) {
            $authority .= sprintf(':%d', $serverPort);
        }

        return $authority;
    }

    /**
     * Get the URL as a string.
     */
    public function toString()
    {
        return $this->getAuthority().$this->getRequestUri();
    }

    /**
     * Get the URL as a string if it is coerced to string.
     */
    public function __toString()
    {
        return $this->toString();
    }

    private static function extractScheme(array $srv)
    {
        // prefer REQUEST_SCHEME variable if set
        if (array_key_exists('REQUEST_SCHEME', $srv)) {
            return $srv['REQUEST_SCHEME'];
        }

        // fallback to HTTPS variable
        if (array_key_exists('HTTPS', $srv)) {
            if ('' !== $srv['HTTPS'] && 'off' !== $srv['HTTPS']) {
                return 'https';
            }

            return 'http';
        }

        // default to "http" if we cannot find anything
        return 'http';
    }

    private static function extractPathInfo(array $srv)
    {
        if (array_key_exists('PATH_INFO', $srv)) {
            return $srv['PATH_INFO'];
        }

        // On CentOS 7 with PHP 5.4 PATH_INFO is null when rewriting is
        // enabled and you go to the root. On Fedora 22 with PHP 5.6
        // PATH_INFO is '/' in the same scenario.
        return '/';
    }
}
