#!/usr/bin/env php
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
use fkooman\SAML\DS\Parser;
use fkooman\SAML\DS\TwigTpl;

try {
    $config = Config::fromFile(sprintf('%s/config/config.php', dirname(__DIR__)));
    $metadataFiles = glob(sprintf('%s/config/metadata/*.xml', dirname(__DIR__)));
    $parser = new Parser($metadataFiles);

    foreach (array_keys($config->spList->asArray()) as $entityID) {
        // convert all special characters in entityID to _ (same method as mod_auth_mellon)
        $encodedEntityID = preg_replace('/__*/', '_', preg_replace('/[^A-Za-z.]/', '_', $entityID));
        $entityDescriptors = $parser->generateMetadata($config->spList->$entityID->idpList);
        $twigTpl = new TwigTpl(
            [
                sprintf('%s/views', dirname(__DIR__)),
            ]
        );
        $metadataContent = $twigTpl->render(
            'metadata',
            [
                'entityDescriptors' => $entityDescriptors,
            ]
        );
        $metadataFile = sprintf('%s/data/%s.xml', dirname(__DIR__), $encodedEntityID);
        if (false === @file_put_contents($metadataFile, $metadataContent)) {
            throw new RuntimeException(sprintf('unable to write "%s"', $metadataFile));
        }

        // for the idpList we do not want the certificate
        foreach ($entityDescriptors as $k => $v) {
            $entityDescriptors[$k]['encodedEntityID'] = preg_replace('/__*/', '_', preg_replace('/[^A-Za-z.]/', '_', $k));
            unset($entityDescriptors[$k]['signingCert']);
            unset($entityDescriptors[$k]['SSO']);
        }

        $idpListFile = sprintf('%s/data/%s.json', dirname(__DIR__), $encodedEntityID);
        if (false === @file_put_contents($idpListFile, json_encode($entityDescriptors))) {
            throw new RuntimeException(sprintf('unable to write "%s"', $idpListFile));
        }
    }
} catch (Exception $e) {
    echo sprintf('ERROR: %s', $e->getMessage()).PHP_EOL;
    exit(1);
}
