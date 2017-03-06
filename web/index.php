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

    // XXX validate
    // the return URI
    $returnUrl = $_GET['return'];
    // the parameter we need to put the IdP entityID in
    $idpKey = $_GET['returnIDParam'];
//    $returnUrl = 'http://localhost/foo?def=bar';
//    $idpKey = 'XYZ';

    $discoEntities = [];
    foreach ($entityDescriptors as $entityDescriptor) {
        $queryString = http_build_query([$idpKey => $entityDescriptor['entityId']]);
        $discoEntities[] = [
            'displayName' => $entityDescriptor['displayName'],
            'idpLogo' => $entityDescriptor['idpLogo'],
            'returnTo' => sprintf('%s&%s', $returnUrl, $queryString),
        ];
    }

    echo $twigTpl->render(
        'discovery',
        [
            'discoEntities' => $discoEntities,
        ]
    );
} catch (Exception $e) {
    echo sprintf('ERROR: %s', $e->getMessage()).PHP_EOL;
    exit(1);
}
