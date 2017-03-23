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

use fkooman\SAML\DS\TwigTpl;

try {
    // validate request (returnIDParam)
    if (!array_key_exists('returnIDParam', $_GET)) {
        throw new RuntimeException('missing "returnIDParam"');
    }
    $returnIDParam = $_GET['returnIDParam'];
    if (1 !== preg_match('/^[a-zA-Z]+$/', $returnIDParam)) {
        throw new RuntimeException('invalid "returnIDParam"');
    }
    // validate request (return)
    if (!array_key_exists('return', $_GET)) {
        throw new RuntimeException('missing "return"');
    }
    $return = $_GET['return'];
    // XXX we probably need to be more strict regarding this URL
    if (false === filter_var($return, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED | FILTER_FLAG_PATH_REQUIRED | FILTER_FLAG_QUERY_REQUIRED)) {
        throw new RuntimeException('invalid "return"');
    }

    $twigTpl = new TwigTpl(
        [
            sprintf('%s/views', dirname(__DIR__)),
        ]
    );

    $discoveryFile = sprintf('%s/data/discovery.json', dirname(__DIR__));
    if (false === $jsonData = @file_get_contents($discoveryFile)) {
        throw new RuntimeException(sprintf('unable to read "%s"', $discoveryFile));
    }

    $entityDescriptors = json_decode($jsonData, true);

    // check if we have a filter
    $filter = null;
    if ('POST' === $_SERVER['REQUEST_METHOD']) {
        if (array_key_exists('entityId', $_POST)) {
            // entry chosen
            // XXX record it for later (save in cookie)

            setcookie(
                'entityId',
                $_POST['entityId'],
                time() + 60 * 60 * 24 * 365,    // remember for 1 year
                '',
                '',
                false,
                true
            );

            $queryString = http_build_query([$returnIDParam => $_POST['entityId']]);
            $returnTo = sprintf('%s&%s', $return, $queryString);
            http_response_code(302);
            header(sprintf('Location: %s', $returnTo));
            exit(0);
        }

        if (array_key_exists('filter', $_POST)) {
            $filter = !empty($_POST['filter']) ? $_POST['filter'] : null;
        }
    }

    $lastChosen = false;
    $chosenInfo = [];
    if (array_key_exists('entityId', $_COOKIE)) {
        $lastChosen = true;
    }

    $discoEntities = [];
    foreach ($entityDescriptors as $entityDescriptor) {
        // use non-JS filter
        if (!is_null($filter)) {
            $searchString = $entityDescriptor['displayName'].implode('', $entityDescriptor['keywords']);
            if (false === stripos($searchString, $filter)) {
                continue;
            }
        }

        $queryString = http_build_query([$returnIDParam => $entityDescriptor['entityId']]);

        if ($lastChosen) {
            if ($entityDescriptor['entityId'] === $_COOKIE['entityId']) {
                $chosenInfo = [
                    'entityId' => $entityDescriptor['entityId'],
                    'displayName' => $entityDescriptor['displayName'],
                    'idpLogo' => $entityDescriptor['idpLogo'],
                ];
                continue;
            }
        }

        $discoEntities[] = [
            'entityId' => $entityDescriptor['entityId'],
            'displayName' => $entityDescriptor['displayName'],
            'idpLogo' => $entityDescriptor['idpLogo'],
            'keywords' => $entityDescriptor['keywords'],
        ];
    }

    echo $twigTpl->render(
        'discovery',
        [
            'lastChosen' => $lastChosen,
            'chosenInfo' => $chosenInfo,
            'discoEntities' => $discoEntities,
        ]
    );
} catch (Exception $e) {
    echo sprintf('ERROR: %s', $e->getMessage()).PHP_EOL;
    exit(1);
}
