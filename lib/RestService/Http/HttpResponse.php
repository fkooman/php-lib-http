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

namespace RestService\Http;

class HttpResponse
{
    private $_headers;
    private $_content;
    private $_statusCode;
    private $_statusCodes = array(
        100 => "Continue",
        101 => "Switching Protocols",
        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-Authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",
        300 => "Multiple Choices",
        301 => "Moved Permanently",
        302 => "Found",
        303 => "See Other",
        304 => "Not Modified",
        305 => "Use Proxy",
        306 => "(Unused)",
        307 => "Temporary Redirect",
        400 => "Bad Request",
        401 => "Unauthorized",
        402 => "Payment Required",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required",
        408 => "Request Timeout",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed",
        413 => "Request Entity Too Large",
        414 => "Request-URI Too Long",
        415 => "Unsupported Media Type",
        416 => "Requested Range Not Satisfiable",
        417 => "Expectation Failed",
        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Timeout",
        505 => "HTTP Version Not Supported"
    );
    private $_useXSendfile;

    public function __construct($statusCode = 200, $contentType = "text/html")
    {
        $this->_headers = array();
        $this->setStatusCode($statusCode);
        $this->setContentType($contentType);
        $this->setContent(NULL);
        $this->setContentFile(NULL);
        $this->useXSendfile(FALSE);
    }

    public function useXSendfile($useXSendfile)
    {
        $this->_useXSendfile = $useXSendfile;
    }

    public function getContent()
    {
        return $this->_content;
    }

    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    public function getStatusReason()
    {
        return $this->_statusCodes[$this->_statusCode];
    }

    public function setContentType($contentType)
    {
        $this->setHeader("Content-Type", $contentType);
    }

    public function getContentType()
    {
        return $this->getHeader("Content-Type");
    }

    public function setContent($content)
    {
        $this->_content = $content;
    }

    public function getContentFile()
    {
        return $this->_contentFile;
    }

    public function setContentFile($contentFile)
    {
        $this->_contentFile = $contentFile;
    }

    public function setStatusCode($code)
    {
        if (!is_numeric($code) || !array_key_exists($code, $this->_statusCodes)) {
            throw new HttpResponseException("invalid status code");
        }
        $this->_statusCode = (int) $code;
    }

    public function setHeaders(array $headers)
    {
        foreach ($headers as $k => $v) {
            $this->setHeader($k, $v);
        }
    }

    public function setHeader($headerKey, $headerValue)
    {
        $foundHeaderKey = $this->_getHeaderKey($headerKey);
        if ($foundHeaderKey === NULL) {
            $this->_headers[$headerKey] = $headerValue;
        } else {
            $this->_headers[$foundHeaderKey] = $headerValue;
        }
    }

    public function getHeader($headerKey)
    {
        $headerKey = $this->_getHeaderKey($headerKey);

        return $headerKey !== NULL ? $this->_headers[$headerKey] : NULL;
    }

    /**
     * Look for a header in a case insensitive way. It is possible to have a
     * header key "Content-type" or a header key "Content-Type", these should
     * be treated as the same.
     *
     * @param headerName the name of the header to search for
     * @returns The name of the header as it was set (original case)
     *
     */
    protected function _getHeaderKey($headerKey)
    {
        $headerKeys = array_keys($this->_headers);
        $keyPositionInArray = array_search(strtolower($headerKey), array_map('strtolower', $headerKeys));

        return ($keyPositionInArray === FALSE) ? NULL : $headerKeys[$keyPositionInArray];
    }

    public function getHeaders($formatted = FALSE)
    {
        if (!$formatted) {
            return $this->_headers;
        }
        $hdrs = array();
        foreach ($this->_headers as $k => $v) {
            array_push($hdrs, $k . ": " . $v);
        }

        return $hdrs;
    }

    public function getStatusLine()
    {
        return "HTTP/1.1 " . $this->getStatusCode() . " " . $this->getStatusReason();
    }

    public function sendResponse()
    {
        header($this->getStatusLine());
        foreach ($this->getHeaders() as $k => $v) {
            header($k . ": " . $v);
        }
        if (NULL !== $this->getContentFile()) {
            if ($this->_useXSendfile) {
                // use X-Sendfile (see https://tn123.org/mod_xsendfile/)
                header("X-Sendfile: " . $this->getContentFile());
            } else {
                // just use PHP to send it (less efficient than X-Sendfile)
                header("Content-Length: " . filesize($this->getContentFile()));
                readfile($this->getContentFile());
            }
        } else {
            echo $this->getContent();
        }
    }

    /**
     * Construct the HttpResponse from a file, you can create the
     * dumps from actual traffic using "curl -i http://www.example.org > dump.txt"
     */
    public static function fromFile($file)
    {
        $data = @file_get_contents($file);
        if (FALSE === $data) {
            throw new HttpResponseException("unable to read file");
        }
        $response = new self();

        // separate the headers from the content
        list($headerLines, $contentData) = explode("\r\n\r\n", $data);

        $headerLinesArray = explode("\r\n", $headerLines);

        // First header is HTTP response code, e.g.: HTTP/1.1 200 OK
        $responseCode = substr($headerLinesArray[0], 9, 3);
        $response->setStatusCode($responseCode);

        unset($headerLinesArray[0]);
        foreach ($headerLinesArray as $headerLine) {
            list($k, $v) = explode(":", $headerLine);
            $response->setHeader(trim($k), trim($v));
        }
        $response->setContent($contentData);

        return $response;
    }

    public function __toString()
    {
        $s  = PHP_EOL;
        $s .= "*HttpResponse*" . PHP_EOL;
        $s .= "Status:" . PHP_EOL;
        $s .= "\t" . $this->getStatusLine() . PHP_EOL;
        $s .= "Headers:" . PHP_EOL;
        foreach ($this->getHeaders() as $k => $v) {
            $s .= "\t" . ($k . ": " . $v) . PHP_EOL;
        }
        $s .= "Content:" . PHP_EOL;
        $s .= $this->getContent();

        return $s;
    }

}
