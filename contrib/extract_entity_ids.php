<?php

try {
    if (false === $md = @simplexml_load_file($argv[1])) {
        throw new RuntimeException(sprintf('unable to read file "%s"', $argv[1]));
    }

    $idpEntityIDs = [];
    $entityDescriptors = $md->xpath('//md:EntityDescriptor');

    foreach ($entityDescriptors as $entityDescriptor) {
        $ssoCount = count($entityDescriptor->xpath('md:IDPSSODescriptor'));
        if (0 !== $ssoCount) {
            $idpEntityIDs[] = (string) $entityDescriptor['entityID'];
        }
    }

    var_export($idpEntityIDs);
} catch (Exception $e) {
    echo sprintf('ERROR: %s', $e->getMessage());
    exit(1);
}
