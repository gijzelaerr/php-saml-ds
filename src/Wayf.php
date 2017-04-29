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

use fkooman\SAML\DS\Http\CookieInterface;
use fkooman\SAML\DS\Http\Exception\HttpException;
use fkooman\SAML\DS\Http\Request;
use fkooman\SAML\DS\Http\Response;
use RuntimeException;

class Wayf
{
    /** @var Config */
    private $config;

    /** @var TplInterface */
    private $tpl;

    /** @var Http\CookieInterface */
    private $cookie;

    /** @var string */
    private $dataDir;

    public function __construct(Config $config, TplInterface $tpl, CookieInterface $cookie, $dataDir)
    {
        $this->config = $config;
        $this->tpl = $tpl;
        $this->cookie = $cookie;
        $this->dataDir = $dataDir;
    }

    /**
     * @return Http\Response
     */
    public function run(Request $request)
    {
        try {
            Validate::request($request);
            $requestMethod = strtolower($request->getMethod());

            return $this->$requestMethod($request);
        } catch (HttpException $e) {
            return new Response(
                $e->getCode(),
                $e->getHeaders(),
                $this->tpl->render(
                    'error',
                    [
                        'errorCode' => $e->getCode(),
                        'errorMessage' => $e->getMessage(),
                    ]
                )
            );
        }
    }

    /**
     * @return Http\Response
     */
    private function get(Request $request)
    {
        // begin input
        $spEntityID = $request->getQueryParameter('entityID');
        $returnIDParam = $request->getQueryParameter('returnIDParam');
        $return = $request->getQueryParameter('return');
        $filter = false;
        if ($request->hasQueryParameter('filter')) {
            $filter = $request->getQueryParameter('filter');
        }
        // end input

        $idpList = $this->getIdPList($spEntityID);
        // XXX what if the count is 0?
        if (1 === count($idpList)) {
            // we only have 1 IdP, so redirect immediately back to the SP
            $idpEntityID = array_keys($idpList)[0];

            return $this->returnTo($return, $returnIDParam, $idpEntityID);
        }

        // XXX make sure there is a displayName!
        $displayName = $this->config->spList->$spEntityID->displayName;

        // do we have an already previous chosen IdP?
        $lastChosen = false;
        if ($this->cookie->has('entityID')) {
            $idpEntityID = $this->cookie->get('entityID');
            if (in_array($idpEntityID, $this->config->spList->$spEntityID->idpList->asArray())) {
                $lastChosen = $idpList[$idpEntityID];
                // remove the last chosen IdP from the list of IdPs
                unset($idpList[$idpEntityID]);
            }
        }

        if ($filter) {
            // remove entries not matching the value in filter
            $idpListCount = count($idpList);
            foreach ($idpList as $k => $v) {
                $inKeywords = false !== stripos(implode(' ', $v['keywords']), $filter);
                if (!$inKeywords) {
                    unset($idpList[$k]);
                }
            }
        }

        $discoveryPage = $this->tpl->render(
            'discovery',
            [
                'useLogos' => $this->config->useLogos,
                'filter' => $filter,
                'entityID' => $spEntityID,
                'encodedEntityID' => self::encodeEntityID($spEntityID),
                'returnIDParam' => $returnIDParam,
                'return' => $return,
                'displayName' => $displayName,
                'lastChosen' => $lastChosen,
                'idpList' => array_values($idpList),
            ]
        );

        return new Response(200, [], $discoveryPage);
    }

    /**
     * @return Http\Response
     */
    private function post(Request $request)
    {
        // begin input
        $spEntityID = $request->getQueryParameter('entityID');
        $returnIDParam = $request->getQueryParameter('returnIDParam');
        $return = $request->getQueryParameter('return');
        $idpEntityID = $request->getPostParameter('idpEntityID');
        // end input

        $idpList = $this->getIdPList($spEntityID);

        // XXX can we not idplist for that? Do we even need IdPList?
        if (!in_array($idpEntityID, $this->config->spList->$spEntityID->idpList->asArray())) {
            throw new HttpException(
                sprintf('the IdP "%s" is not listed for this SP', $idpEntityID),
                400
            );
        }

        $this->cookie->set('entityID', $idpEntityID);

        return $this->returnTo($return, $returnIDParam, $idpEntityID);
    }

    /**
     * @param string $entityID
     *
     * @return string
     */
    private static function encodeEntityID($entityID)
    {
        return preg_replace('/__*/', '_', preg_replace('/[^A-Za-z.]/', '_', $entityID));
    }

    /**
     * @param string $spEntityID
     *
     * @return array
     */
    private function getIdPList($spEntityID)
    {
        // make sure the SP exists in the config
        if (!isset($this->config->spList->$spEntityID)) {
            throw new HttpException(
                sprintf('SP with entityID "%s" not registered in discovery service', $spEntityID),
                400
            );
        }

        // load the IdP List of this SP
        $encodedEntityID = self::encodeEntityID($spEntityID);
        $idpListFile = sprintf('%s/%s.json', $this->dataDir, $encodedEntityID);
        if (false === $jsonData = @file_get_contents($idpListFile)) {
            throw new RuntimeException(sprintf('unable to read "%s"', $idpListFile));
        }

        $idpList = json_decode($jsonData, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new RuntimeException(sprintf('unable to decode "%s"', $idpListFile));
        }

        // XXX maybe start on array_values of idpList?
        // XXX check return value?
        uasort($idpList, function ($a, $b) {
            // XXX make sure they have the field 'displayName'!
            return strcasecmp($a['displayName'], $b['displayName']);
        });

        return $idpList;
    }

    /**
     * @return Http\Response
     */
    private function returnTo($return, $returnIDParam, $idpEntityID)
    {
        $returnTo = sprintf(
            '%s&%s',
            $return,
            http_build_query(
                [
                    $returnIDParam => $idpEntityID,
                ]
            )
        );

        return new Response(302, ['Location' => $returnTo]);
    }
}
