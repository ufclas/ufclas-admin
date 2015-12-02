UF CLAS Admin Tools
====================

Description
----------------

This is still a work in progress!

Author: Priscilla Chapman (CLAS IT)
Author URI: http://it.clas.ufl.edu/
Git project link: https://bitbucket.org/ufclasit/ufclas-admin
Please contact CLAS IT for support.

Requirements
-------------
* WordPress Multisite

Installation
------------------

Changelog
---------

* [0.5.0] Adds a theme upgrade page to help update to ufclaspeople2, adds DataTables library to the repo
* [0.4.0] Adds a Site Forms screen to list forms for each site
* [0.3.0] Adds Site Users page that lists all users, site access, and role. Removes wp-list-table pages.
* [0.2.1] Adds TableTools extension to copy, save, print table
* [0.2.0] Reorganizes styles, adds DataTables, ajax, and transients support
* [0.1.1] Adds a reports menu item with list of information about sites
* [0.0.1] Initial commit

To-Do List
----------

* Initial Admin page is blank, add an overview screen with total counts
* There aren't any hooks when info is updated. Need a button to clear the transient manually
* Domain-mapped links end up being https instead of http, might just link title to dashboard or force http scheme
* Blank page when loading a huge list of sites. Need a loading message/spinner
* Path is blank for the main site. Need to stop trimming the / from path
* Need to add a screen for archived/deleted sites to clean up

Notes
-----

* Saving data into separate transient arrays so each screen can reuse the main site list
* Handling data for a network with hundreds of sites?