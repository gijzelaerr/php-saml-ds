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
require_once sprintf('%s/vendor/autoload.php', dirname(__DIR__));

use fkooman\SAML\DS\Config;
use fkooman\SAML\DS\Http\HttpCookie;
use fkooman\SAML\DS\Http\Request;
use fkooman\SAML\DS\Http\Response;
use fkooman\SAML\DS\TwigTpl;
use fkooman\SAML\DS\Wayf;

set_error_handler(
    function ($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            // This error code is not included in error_reporting
            return;
        }
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
);

try {
    $config = Config::fromFile(sprintf('%s/config/config.php', dirname(__DIR__)));
    $templateCache = null;
    if ($config->get('enableTemplateCache')) {
        $templateCache = sprintf('%s/data/tpl', dirname(__DIR__));
    }

    $twigTpl = new TwigTpl(
        [
            sprintf('%s/views', dirname(__DIR__)),
            sprintf('%s/config/views', dirname(__DIR__)),
        ],
        $templateCache
    );

    $request = new Request($_SERVER, $_GET, $_POST);
    $cookie = new HttpCookie(
        [
            'SameSite' => 'Lax',
            'Secure' => $config->get('secureCookie'),
            'Max-Age' => 60 * 60 * 24 * 365,   // 1Y
            'Path' => $request->getRoot(),
        ]
    );
    $wayf = new Wayf($config, $twigTpl, $cookie, sprintf('%s/data', dirname(__DIR__)));
    $wayf->run($request)->send();
} catch (Exception $e) {
    $errorMessage = sprintf('[500] (%s): %s', get_class($e), $e->getMessage());
    $response = new Response(
        500,
        ['Content-Type' => 'text/plain'],
        htmlentities($errorMessage, ENT_QUOTES, 'UTF-8')
    );
    $response->send();
    error_log(sprintf('%s {%s}', $errorMessage, $e->getTraceAsString()));
}
