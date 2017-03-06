# Introduction

This is a SAML discovery service written in PHP.

It follows 
[Identity Provider Discovery Service Protocol and Profile](https://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-idp-discovery.pdf) 
because [mod_auth_mellon](https://github.com/UNINETT/mod_auth_mellon) 
seems to use that.

# What

So what does this software do?

1. Take eduGAIN metadata file;
2. Read a configuration files that contains a list of IdP entityIds that 
   become part of the discovery page, i.e. IdPs that have access to the 
   service;
3. Write a simple PHP file that contains the discovery page and responds 
   according to the "Identity Provider Discovery Service Protocol and Profile";
4. Write a stripped down XML metadata file for use by mod_auth_mellon

That's it. No multi SP support, no JavaScript, no pipelines, no crap.

# Requirements

Just PHP >= 5.4.

In addition, it is assumed that the eduGAIN metadata is available 
and *verified*. See the `contrib/` directory for a simple script to do this.

# Why?

I found some other options when investigating how to do SAML discovery:

* [PyFF](https://github.com/leifj/pyFF/)
* [DiscoJuice](http://discojuice.org/)

I am not sure what PyFF does. It seems to do _everything_, but I don't know 
what exactly, how or why. Apparently it can create discovery pages as well in 
HTML, but couldn't figure out how. I found 
[this](https://wiki.surfnet.nl/pages/viewpage.action?pageId=50106503), but that 
didn't really seem like a lightweight simple approach.

DiscoJuice is not ready for production use it says, whatever that means, and 
I saw a reference the `jquery.js` you need to embed when you want to use it, 
so that one is out too.

# Use

The software consists of two parts:

1. A metadata extractor (for use with the DS and with mod_auth_mellon);
2. A web script that works as a discovery service

The extractor takes the (validated) eduGAIN metadata and extracts the IdPs with
the provided entityIds. It will extract the display name and logo from the 
metadata which are relevant for the discovery page and write a stripped 
metadata file for consumption by mod_auth_mellon.

Edit the configuration file in `config/config.php` and set the entityIds you
want to show on the discovery page.

Configure mod_auth_mellon to use the generated metadata file.
