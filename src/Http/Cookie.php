<?php
/**
 * Copyright 2017 François Kooman <fkooman@tuxed.net>.
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

namespace fkooman\SAML\DS\Http;

use fkooman\SAML\DS\Http\Exception\CookieException;

class Cookie
{
    /** @var string */
    private $domain;

    /** @var string */
    private $path;

    /** @var bool */
    private $secure;

    /**
     * @param string $domain
     * @param string $path
     * @param bool   $secure
     */
    public function __construct($domain, $path, $secure)
    {
        $this->domain = $domain;
        $this->path = $path;
        $this->secure = (bool) $secure;
    }

    public function __set($name, $value)
    {
        $cookieResult = setcookie(
            $name,
            $value,
            time() + 60 * 60 * 24 * 365, // expire
            $this->path,
            $this->domain,
            $this->secure,
            true // httponly
        );

        if (false === $cookieResult) {
            throw new CookieException('unable to set cookie');
        }
    }

    public function __isset($key)
    {
        // XXX does $_COOKIE always exist?
        return array_key_exists($key, $_COOKIE);
    }

    public function __get($key)
    {
        // XXX does $_COOKIE always exist?
        if (!array_key_exists($key, $_COOKIE)) {
            throw new CookieException(sprintf('unable to get cookie value for "%s"', $key));
        }

        return $_COOKIE[$key];
    }
}
