# UF CLAS Admin Tools #

This is still a work in progress! 

## To-Do List

* Initial Admin page is blank, add an overview screen with total counts
* There aren't any hooks when info is updated. Need a button to clear the transient manually
* Domain-mapped links end up being https instead of http, might just link title to dashboard or force http scheme
* Blank page when loading a huge list of sites. Need a loading message/spinner
* Path is blank for the main site. Need to stop trimming the / from path

## Features to Add ##

* Need to add a screen to list active forms (identify fields for possible restricted info?)
* Need to add a screen for archived/deleted sites to clean up
* Need to add a screen for users and roles for audits

## Notes ##

* Saving data into separate transient arrays so each screen can reuse the main site list
* Handling data for a network with hundreds of sites?