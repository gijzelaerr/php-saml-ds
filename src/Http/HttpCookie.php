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

class HttpCookie implements CookieInterface
{
    /** @var array */
    private $cookieOptions;

    /**
     * @param array $cookieOptions
     */
    public function __construct(array $cookieOptions = [])
    {
        $this->cookieOptions = array_merge(
            [
                // defaults
                'Secure' => true,       // bool
                'HttpOnly' => true,     // bool
                'Path' => '/',          // string
                'Domain' => null,       // string
                'Max-Age' => null,      // int > 0
                'SameSite' => 'Strict', // "Strict|Lax"
            ],
            $cookieOptions
        );
    }

    public function delete($name)
    {
        self::set($name, '');
    }

    public function get($name)
    {
        if (!array_key_exists($name, $_COOKIE)) {
            throw new CookieException(sprintf('unable to get cookie value for "%s"', $name));
        }

        return $_COOKIE[$name];
    }

    public function has($name)
    {
        return array_key_exists($name, $_COOKIE);
    }

    public function set($name, $value)
    {
        $attributeValueList = [];
        if ($this->cookieOptions['Secure']) {
            $attributeValueList[] = 'Secure';
        }
        if ($this->cookieOptions['HttpOnly']) {
            $attributeValueList[] = 'HttpOnly';
        }
        $attributeValueList[] = sprintf('Path=%s', $this->cookieOptions['Path']);
        if (!is_null($this->cookieOptions['Domain'])) {
            $attributeValueList[] = sprintf('Domain=%s', $this->cookieOptions['Domain']);
        }

        if (!is_null($this->cookieOptions['Max-Age'])) {
            $attributeValueList[] = sprintf('Max-Age=%d', $this->cookieOptions['Max-Age']);
        }
        $attributeValueList[] = sprintf('SameSite=%s', $this->cookieOptions['SameSite']);

        header(
            sprintf(
                'Set-Cookie: %s=%s; %s',
                $name,
                $value,
                implode('; ', $attributeValueList)
            ),
            false
        );
    }
}
