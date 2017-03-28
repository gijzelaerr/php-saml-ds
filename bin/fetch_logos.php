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

@mkdir(sprintf('%s/data/logo/idp', dirname(__DIR__)), 0711, true);

try {
    $logoListFile = sprintf('%s/data/logoList.json', dirname(__DIR__));
    if (false === $jsonData = @file_get_contents($logoListFile)) {
        throw new RuntimeException(sprintf('unable to read "%s"', $logoListFile));
    }

    $entityLogoList = json_decode($jsonData, true);
    // XXX json error handling
    foreach ($entityLogoList as $entityID => $logoList) {
        $encodedEntityID = preg_replace('/__*/', '_', preg_replace('/[^A-Za-z.]/', '_', $entityID));

        // we take the logo with the highest width, assuming it will be the best quality...
        usort($logoList, function ($a, $b) {
            return $a['width'] < $b['width'] ? -1 : ($a['width'] > $b['width'] ? 1 : 0);
        });

        $logoUri = $logoList[count($logoList) - 1]['uri'];
        echo $logoUri.PHP_EOL;

        // check if it is actually a URL or maybe a base64 encoded string?
        if (false === filter_var($logoUri, FILTER_VALIDATE_URL)) {
            // not a URL
            // is it a DATA URL?
            if (0 !== strpos($logoUri, 'data:')) {
                // XXX invalid logo!
                $logoData = false;
            } else {
                $encodedLogo = explode(',', $logoUri, 2)[1];
                // XXX error handling decode
                $logoData = base64_decode($encodedLogo);
            }
        } else {
            $ctx = stream_context_create(
                [
                    'http' => ['timeout' => 5],
                    'https' => ['timeout' => 5],
                ]
            );

            $logoData = @file_get_contents($logoUri, false, $ctx);
        }

        $logoFile = sprintf('%s/data/logo/idp/%s.orig', dirname(__DIR__), $encodedEntityID);
        if (false === @file_put_contents($logoFile, $logoData)) {
            throw new RuntimeException(sprintf('unable to write "%s"', $logoFile));
        }
    }
} catch (Exception $e) {
    echo sprintf('ERROR: %s', $e->getMessage()).PHP_EOL;
    exit(1);
}
