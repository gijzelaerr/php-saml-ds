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
use fkooman\SAML\DS\Http\Cookie;
use fkooman\SAML\DS\Http\Exception\HttpException;
use fkooman\SAML\DS\Http\Request;
use fkooman\SAML\DS\Http\Response;
use fkooman\SAML\DS\TwigTpl;
use fkooman\SAML\DS\Validate;

try {
    $config = Config::fromFile(sprintf('%s/config/config.php', dirname(__DIR__)));
    $request = new Request($_SERVER, $_GET, $_POST);

    list($entityID, $returnIDParam, $return, $filter) = Validate::queryParameters($request, $config);
    $filter = empty($filter) ? false : $filter;

    $displayName = $config->spList->$entityID->displayName;

    $twigTpl = new TwigTpl(
        [
            sprintf('%s/views', dirname(__DIR__)),
        ]
    );

    $cookie = new Cookie($request->getServerName(), $request->getRoot(), $config->secureCookie);

    // load the IdP List of thie SP
    $spFileName = str_replace(['://', '/'], ['_', '_'], $entityID);
    $idpListFile = sprintf('%s/data/%s.json', dirname(__DIR__), $spFileName);
    if (false === $jsonData = @file_get_contents($idpListFile)) {
        throw new RuntimeException(sprintf('unable to read "%s"', $idpListFile));
    }
    // XXX check if json_decode worked
    $idpList = json_decode($jsonData, true);

    if ('GET' === $request->getMethod()) {
        // display the WAYF page
        $lastChosen = false;
        if (isset($cookie->entityID) && !$filter) {
            if (array_key_exists($cookie->entityID, $idpList)) {
                $lastChosen = $idpList[$cookie->entityID];
                // remove the last chosen IdP from the list of IdPs
                unset($idpList[$cookie->entityID]);
            }
        }

        // XXX maybe start on array_values of idpList?
        // XXX check return value?
        usort($idpList, function ($a, $b) {
            // XXX make sure they have the field 'displayName'!
            return strcmp($a['displayName'], $b['displayName']);
        });

        if ($filter) {
            $lastChosen = false;
            // remove entries not matching the value in filter
            $idpListCount = count($idpList);
            for ($i = 0; $i < $idpListCount; ++$i) {
                $inKeywords = false !== stripos(implode(' ', $idpList[$i]['keywords']), $filter);
                $inDisplayName = false !== stripos($idpList[$i]['displayName'], $filter);
                if (!$inKeywords && !$inDisplayName) {
                    unset($idpList[$i]);
                }
            }
            $idpList = array_values($idpList);
        }

        $discoveryPage = $twigTpl->render(
            'discovery',
            [
                'useLogos' => $config->useLogos,
                'filter' => $filter,
                'entityID' => $entityID,
                'returnIDParam' => $returnIDParam,
                'return' => $return,
                'displayName' => $displayName,
                'lastChosen' => $lastChosen,
                'idpList' => $idpList,
            ]
        );

        $response = new Response(200, [], $discoveryPage);
        $response->send();
        exit(0);
    }

    if ('POST' === $request->getMethod()) {
        // an IdP was chosen
        $idpEntityID = $request->getPostParameter('idpEntityID');
        if (!array_key_exists($idpEntityID, $idpList)) {
            throw new HttpException(
                sprintf('the IdP "%s" is not listed for this SP', $idpEntityID),
                400
            );
        }

        $cookie->entityID = $idpEntityID;

        $returnTo = sprintf(
            '%s&%s',
            $return,
            http_build_query(
                [
                    $returnIDParam => $idpEntityID,
                ]
            )
        );

        $response = new Response(302, ['Location' => $returnTo]);
        $response->send();
        exit(0);
    }
} catch (HttpException $e) {
    $response = new Response($e->getCode(), [], $e->getMessage());
    $response->send();
} catch (Exception $e) {
    $response = new Response(500, [], $e->getMessage());
    $response->send();
}
