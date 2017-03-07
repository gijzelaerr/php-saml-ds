#!/bin/sh

curl -L -o config/eduGAIN.xml http://mds.edugain.org/ && \
#curl -L -O https://technical.edugain.org/mds-2014.cer && \
xmlsec1 --verify --id-attr:ID urn:oasis:names:tc:SAML:2.0:metadata:EntitiesDescriptor --trusted-pem contrib/mds-2014.cer config/eduGAIN.xml
