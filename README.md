UFCLAS Admin Tools
==================

Description
-----------

Adds a CLAS IT Admin section to the network admin dashboard with unit-specific reports. Uses DataTables and D3.js libraries.

Requirements
-------------
* WordPress Multisite

Installation
------------
* Network activate. Go to the network admin dashboard to find the new menu item.

Updating Network Data
---------------------

Forced updates to values using WP-CLI

```PHP
> php wp-cli.phar transient delete ufclas_admin_siteinfo --network
> php wp-cli.phar transient delete ufclas_admin_sites --network
> php wp-cli.phar transient delete ufclas_admin_siteforms --network
```

Sites By Status

```
> php wp-cli.phar transient delete ufclas_admin --network
```


Changelog
---------

* [0.6.3] Adds post count column to the users screen
* [0.6.2] Adds D3.js chart to the admin page 
* [0.6.1] Adds archived sites, fixes user table headings 
* [0.6.0] Updates version of DataTables, adds bootstrap styles and loading animations 
* [0.5.0] Adds a theme upgrade page to help update to ufclaspeople2, adds DataTables library to the repo
* [0.4.0] Adds a Site Forms screen to list forms for each site
* [0.3.0] Adds Site Users page that lists all users, site access, and role. Removes wp-list-table pages.
* [0.2.1] Adds TableTools extension to copy, save, print table
* [0.2.0] Reorganizes styles, adds DataTables, ajax, and transients support
* [0.1.1] Adds a reports menu item with list of information about sites
* [0.0.1] Initial commit

Notes
-----

* Saves site data into network transients containing arrays of sites
* May need special handling for data with a very large network

To-Do List
----------

* There aren't any hooks when info is updated. Need a button to clear the transient manually
* Path is blank for the main site. Need to stop trimming the / from path
* Add a screen for navigating through domains (WPMU Domain Mapping)
* Convert transient data to JSON?