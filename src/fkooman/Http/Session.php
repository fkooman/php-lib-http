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

class Session implements SessionInterface
{
    /** @var string */
    private $ns;

    /** @var array */
    private $sessionOptions;

    public function __construct($ns = 'MySession', array $sessionOptions = array())
    {
        $this->ns = $ns;

        $defaultOptions = array(
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
        );

        $this->sessionOptions = array_merge($defaultOptions, $sessionOptions);
    }

    private function startSession()
    {
        if ('' === session_id()) {
            // no session active
            session_set_cookie_params(
                $this->sessionOptions['lifetime'],
                $this->sessionOptions['path'],
                $this->sessionOptions['domain'],
                $this->sessionOptions['secure'],
                $this->sessionOptions['httponly']
            );
            session_start();
        }
    }

    public function set($key, $value)
    {
        $this->startSession();
        $_SESSION[$this->ns][$key] = $value;
    }

    public function delete($key)
    {
        $this->startSession();
        if ($this->has($key)) {
            unset($_SESSION[$this->ns][$key]);
        }
    }

    public function has($key)
    {
        $this->startSession();
        if (array_key_exists($this->ns, $_SESSION)) {
            return array_key_exists($key, $_SESSION[$this->ns]);
        }

        return false;
    }

    public function get($key)
    {
        $this->startSession();
        if ($this->has($key)) {
            return $_SESSION[$this->ns][$key];
        }

        return;
    }

    public function destroy()
    {
        $this->startSession();
        if (array_key_exists($this->ns, $_SESSION)) {
            unset($_SESSION[$this->ns]);
        }
    }
}
