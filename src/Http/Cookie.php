<?php
/**
 *  Copyright (C) 2017 SURFnet.
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
