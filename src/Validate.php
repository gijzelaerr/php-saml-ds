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
        if ('IdP' !== $returnIDParam) {
            throw new HttpException('unsupported "returnIDParam"', 400);
        }

        // return MUST be a valid HTTPS URI
        // XXX should we require this to be registered as well?
        $return = $request->getQueryParameter('return');
        $filterFlags = FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED | FILTER_FLAG_PATH_REQUIRED | FILTER_FLAG_QUERY_REQUIRED;
        if (false === filter_var($return, FILTER_VALIDATE_URL, $filterFlags)) {
            throw new HttpException('invalid "return" URL', 400);
        }

        return [$entityID, $returnIDParam, $return];
    }
}
