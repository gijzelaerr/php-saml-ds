# Introduction

This is a SAML discovery service written in PHP.

**NOTE**: this is work in progress! Feel free to test though!

It follows 
[Identity Provider Discovery Service Protocol and Profile](https://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-idp-discovery.pdf) 
because [mod_auth_mellon](https://github.com/UNINETT/mod_auth_mellon) 
seems to support that. So why not?

# Features

* Logo support, extracted from (eduGAIN) metadata;
* Most browsers are supported;
* JS not required (works perfectly well with JS disabled);
* Keyboard navigation using `<TAB>` to select (next/previous) IdP;
* Filter (keyword) support, i.e. only show entries that (string) match the 
  filter you provide, also without JS.

# Browser Support

The discovery service is tested with the following browsers:

* Firefox (latest)
* Google Chrome (latest);
* Safari on iOS (latest);
* Internet Explorer 11;
* Edge;

# Components

## Generator

A generator script that takes SAML metadata file(s) and extracts the IdPs based 
on the entityIDs that are set in the configuration file. It writes out two 
files:

1. A stripped down SAML metadata file containing only the required entries for
   use by mod_auth_mellon;
2. A JSON file containing information about the IdPs for use by the discovery
   service.

The stripped down SAML metadata file is needed because mod_auth_mellon, at 
least the version shipped with CentOS 7, is unusably slow if you use e.g. the 
entire eduGAIN metadata file.

## Discovery Service

A service that used the JSON file to display a discovery page where the user
can select their IdP. See the screenshots.

Without logos:

![screenshot](contrib/screenshot.png)

With logos (optional):

![screenshot](contrib/screenshot_logos.png)

# Requirements

The software is written in PHP, and requires PHP >= 5.4 together with the 
[imagick](https://pecl.php.net/package/imagick) PECL extension. This extension 
is available on RHEL/CentOS (EPEL) and Debian.

# Obtaining Metadata

The `contrib/` directory contains some scripts to download SAML metadata from
eduGAIN, verify the signature and place it in the `config/metadata` directory.

# Configuration

All (source) metadata files you want to use should be placed in the 
`config/metadata` directory and have a `.xml` extension. 

Specify the entityIDs of the IdPs you want to support in the 
`config/config.php` file.

# Running

To run the generator, make sure the metadata files are located in the 
`config/metadata` directory and a writable `data/` directory exists.

    $ php bin/generate.php

This will generate the JSON and XML file mentioned above, and download and 
scale/compress all IdP logos if enabled, and if they are available in the 
metadata file.

# Alternatives

I found some other options when investigating how to do SAML discovery:

* [PyFF](https://github.com/leifj/pyFF/);
* [DiscoJuice](http://discojuice.org/)

They were not really what I wanted.
