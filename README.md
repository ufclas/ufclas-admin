UFCLAS Admin Tools
==================

Adds a section to the network admin dashboard with unit-specific reports. To improve performance, site data will only update every 12 hours. 

- Uses [DataTables](https://datatables.net/) and [D3.js](https://d3js.org/) JavaScript libraries. 
- Assumes that network requires a GatorLink login for to access sites.

### Reports
__Sites by Status (_requires More Privacy Options_)__:
Lists number of sites by visibility status

__Site Information__:
Lists all sites, site title, description, theme, active plugins

__Site Users__:
Lists users by site, includes role and total number of posts

__Site Forms (_requires Gravity Forms_)__:
Lists all forms, includes link to form preview, list of form fields (label, type), notification Emails

__Theme Upgrade (_only if UFCLAS People 1.0 and 2.0 are installed_)__:
List of sites still using old theme

### Supported Plugins
- [More Privacy Options](https://wordpress.org/plugins/more-privacy-options/)
- [Gravity Forms](http://www.gravityforms.com/)

### Updating Site Data

Use the WP-CLI commands below to refresh the cached site data.

```
> wp transient delete ufclas_admin --network
> wp transient delete ufclas_admin_siteinfo --network
> wp transient delete ufclas_admin_sites --network
> wp transient delete ufclas_admin_siteforms --network
> wp transient delete ufclas_admin_siteusers --network
> wp transient delete ufclas_admin_themeupgrade --network
```

Requirements
------------
- WordPress Multisite

Installation
------------
- Network activate. Go to the network admin dashboard to find the new menu 'CLAS Admin' item.


Changelog
---------

### 0.7
- Updates forms table to include field types and links to form preview
- Removes unused site archive file
- Skips using cached site data when WP_DEBUG is true
- Updates DataTables and D3.js to CDN versions 

### 0.6
- Adds post count column to the users screen
- Adds D3.js chart to the admin page for site status graphic
- Adds archived sites, fixes user table headings 
- Updates version of DataTables, adds bootstrap styles and loading animations 

### 0.5
- Adds a theme upgrade page to help update to ufclaspeople2, adds DataTables library to the repo

### 0.4
- Adds a Site Forms screen to list forms for each site

### 0.3
- Adds Site Users page that lists all users, site access, and role. Removes wp-list-table pages.

### 0.2
- [0.2.1] Adds TableTools extension to copy, save, print table
- [0.2.0] Reorganizes styles, adds DataTables, ajax, and transients support

### 0.1
- [0.1.1] Adds a reports menu item with list of information about sites
- [0.0.1] Initial commit


To-Do List
----------

- There aren't any hooks when info is updated. Need a button to clear the transient manually
- Add a screen for navigating through domains (WPMU Domain Mapping)
- Add a screen for archived sites that have not been updated in over a year
- Remove options for More Privacy Options if not activated
- Add option to set the theme upgrade theme name