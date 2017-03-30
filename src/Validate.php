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

use fkooman\SAML\DS\Http\Exception\HttpException;
use fkooman\SAML\DS\Http\Request;

class Validate
{
    public static function queryParameters(Request $request, Config $config)
    {
        // entityID parameter MUST be registered in configuration as an
        // entityID for an SP
        $entityID = $request->getQueryParameter('entityID');
        if (!isset($config->spList->$entityID)) {
            throw new HttpException(
                sprintf('SP with entityID "%s" not registered in discovery service', $entityID),
                400
            );
        }

        // returnIDParam MUST be "IdP" for now
        $returnIDParam = $request->getQueryParameter('returnIDParam');
        if (!in_array($returnIDParam, ['IdP', 'idpentityid'])) {
            throw new HttpException('unsupported "returnIDParam"', 400);
        }

        // return MUST be a valid HTTPS URI
        // XXX should we require this to be registered as well?
        $return = $request->getQueryParameter('return');
        $filterFlags = FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED | FILTER_FLAG_PATH_REQUIRED | FILTER_FLAG_QUERY_REQUIRED;
        if (false === filter_var($return, FILTER_VALIDATE_URL, $filterFlags)) {
            throw new HttpException('invalid "return" URL', 400);
        }

        // filter is optional
        $filter = null;
        if ($request->hasQueryParameter('filter')) {
            $filter = $request->getQueryParameter('filter');
            if (1 !== preg_match('/^[a-zA-Z0-9]*$/', $filter)) {
                throw new HttpException('invalid "filter" string', 400);
            }
        }

        return [$entityID, $returnIDParam, $return, $filter];
    }
}
