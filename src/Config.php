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

namespace fkooman\SAML\DS;

use fkooman\SAML\DS\Exception\ConfigException;

class Config
{
    /** @var array */
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->data)) {
            // consumers MUST check first if a field is available before
            // requesting it
            throw new ConfigException(sprintf('missing field "%s" in configuration', $key));
        }

        if (is_array($this->data[$key])) {
            return new self($this->data[$key]);
        }

        return $this->data[$key];
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return $this->data;
    }

    public static function fromFile($configFile)
    {
        if (false === @file_exists($configFile)) {
            throw new ConfigException(sprintf('unable to read "%s"', $configFile));
        }

        return new static(require $configFile);
    }
}
