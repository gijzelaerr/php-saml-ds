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

use DateInterval;
use DateTime;
use fkooman\SAML\DS\Http\Exception\CookieException;

class Cookie
{
    /** @var array */
    private $cookieOptions;

    /**
     * @param string $domain
     * @param string $path
     * @param bool   $secure
     */
    public function __construct(array $cookieOptions = [])
    {
        $defaultOptions = [
            'domain' => '',
            'path' => '',
            'secure' => true,
        ];

        $this->cookieOptions = array_merge($defaultOptions, $cookieOptions);
    }

    public function __set($name, $value)
    {
        $dateTime = new DateTime();
        $dateTime->add(new DateInterval('P1y'));

        $cookieResult = setcookie(
            $name,
            $value,
            $dateTime->getTimestamp(),
            $this->path,
            $this->domain,
            $this->secure,
            true // httponly
        );

        if (false === $cookieResult) {
            throw new CookieException('unable to set cookie');
        }
    }

    public function __isset($name)
    {
        return array_key_exists($name, $_COOKIE);
    }

    public function __unset($name)
    {
        $this->$name = false;
    }

    public function __get($name)
    {
        if (!array_key_exists($name, $_COOKIE)) {
            throw new CookieException(sprintf('unable to get cookie value for "%s"', $name));
        }

        return $_COOKIE[$name];
    }
}
