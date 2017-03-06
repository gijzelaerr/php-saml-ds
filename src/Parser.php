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

use RuntimeException;
use SimpleXMLElement;

class Parser
{
    /** @var \SimpleXMLElement */
    private $metadata;

    public function __construct($metadataFile)
    {
        if (false === $metadata = @simplexml_load_file($metadataFile)) {
            throw new RuntimeException(sprintf('unable to read file "%s"', $metadataFile));
        }
        $metadata->registerXPathNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');
        $metadata->registerXPathNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        $this->metadata = $metadata;
    }

    /**
     * Generate a simple SAML metadata file for the provided IdP entityIds
     * based on the SAML metadata file provided, e.g. eduGAIN metadata.
     */
    public function generateMetadata(array $entityIdList)
    {
        $entityDescriptors = [];
        foreach ($entityIdList as $entityId) {
            $entityDescriptors[] = $this->extractInfo($entityId);
        }

        return $entityDescriptors;
    }

    /**
     * @return array
     */
    public function extractInfo($entityId)
    {
        $entityInfo = $this->metadata->xpath(
            sprintf('//md:EntityDescriptor[@entityID="%s"]', $entityId)
        );
        $idpDescriptor = $entityInfo[0]->xpath('md:IDPSSODescriptor');

        return [
            'entityId' => $entityId,
            'displayName' => $this->getDisplayName($entityInfo[0]),
            'SSO' => $this->getSSO($idpDescriptor[0]),
            'signingCert' => $this->getSigningCert($idpDescriptor[0]),
        ];
    }

    /**
     * Get the HTTP-Redirect binding.
     *
     * @return string
     */
    private function getSSO(SimpleXMLElement $xml)
    {
        $result = $xml->xpath('md:SingleSignOnService[@Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"]');
        if (0 === count($result)) {
            throw new ParserException('no SingleSignOnService HTTP-Redirect binding');
        }

        return (string) $result[0]['Location'];
    }

    /**
     * Get the signing certificate.
     *
     * @return string
     */
    private function getSigningCert(SimpleXMLElement $xml)
    {
        $result = $xml->xpath('md:KeyDescriptor[@use="signing"]/ds:KeyInfo/ds:X509Data/ds:X509Certificate');
        if (0 === count($result)) {
            // no explicit entry found for "signing", assume the one specified
            // is *also* used for signing, e.g. in the case no explicit "use"
            // is provided
            return $this->getGenericCert($xml);
        }

        return self::trimCert((string) $result[0]);
    }

    /**
     * Get the generic certificate.
     *
     * @return string|null
     */
    private function getGenericCert(SimpleXMLElement $xml)
    {
        $result = $xml->xpath('md:KeyDescriptor/ds:KeyInfo/ds:X509Data/ds:X509Certificate');
        if (0 === count($result)) {
            return null;
        }

        return self::trimCert((string) $result[0]);
    }

    private static function trimCert($certData)
    {
        return str_replace(
            [' ', "\t", "\n", "\r", "\0", "\x0B"],
            '',
            $certData
        );
    }

    private function getDisplayName(SimpleXMLElement $xml)
    {
        $result = $xml->xpath('md:IDPSSODescriptor/md:Extensions/mdui:UIInfo/mdui:DisplayName[@xml:lang="en"]');
        if (0 === count($result)) {
            // try OrganizationDisplayName
            return $this->getOrganizationDisplayName($xml);
        }

        return trim((string) $result[0]);
    }

    private function getOrganizationDisplayName(SimpleXMLElement $xml)
    {
        $result = $xml->xpath('md:Organization/md:OrganizationDisplayName[@xml:lang="en"]');
        if (0 === count($result)) {
            return null;
        }

        return trim((string) $result[0]);
    }
}
