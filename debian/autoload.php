<?php
require_once '/usr/share/php/Twig/autoload.php';
require_once '/usr/share/php/fkooman/SeCookie/autoload.php';

spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'fkooman\\saml\\ds\\config' => '/../src/Config.php',
                'fkooman\\saml\\ds\\exception\\configexception' => '/../src/Exception/ConfigException.php',
                'fkooman\\saml\\ds\\exception\\logoexception' => '/../src/Exception/LogoException.php',
                'fkooman\\saml\\ds\\http\\exception\\httpexception' => '/../src/Http/Exception/HttpException.php',
                'fkooman\\saml\\ds\\http\\request' => '/../src/Http/Request.php',
                'fkooman\\saml\\ds\\http\\response' => '/../src/Http/Response.php',
                'fkooman\\saml\\ds\\httpclient\\curlhttpclient' => '/../src/HttpClient/CurlHttpClient.php',
                'fkooman\\saml\\ds\\httpclient\\httpclientinterface' => '/../src/HttpClient/HttpClientInterface.php',
                'fkooman\\saml\\ds\\httpclient\\response' => '/../src/HttpClient/Response.php',
                'fkooman\\saml\\ds\\logo' => '/../src/Logo.php',
                'fkooman\\saml\\ds\\parser' => '/../src/Parser.php',
                'fkooman\\saml\\ds\\parserexception' => '/../src/ParserException.php',
                'fkooman\\saml\\ds\\tplinterface' => '/../src/TplInterface.php',
                'fkooman\\saml\\ds\\twigtpl' => '/../src/TwigTpl.php',
                'fkooman\\saml\\ds\\validate' => '/../src/Validate.php',
                'fkooman\\saml\\ds\\wayf' => '/../src/Wayf.php'
            );
        }
        $cn = strtolower($class);
        if (isset($classes[$cn])) {
            require __DIR__ . $classes[$cn];
        }
    },
    true,
    false
);
// @codeCoverageIgnoreEnd


