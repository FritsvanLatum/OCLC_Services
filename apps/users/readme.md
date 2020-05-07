# Assets for integration of json-editor and WMS identity management

## Introduction
This repository uses the repository je_assets. 

Installation:
1. Clone this repository somewhere in your html documents directory.
2. Goto the installed directory and clone je_assets as a subdirectory.

The result with je_assets installed is:

| file | description|
|---|---|
| css/           | styles for forms, only *registration.css* is used |
| cssppl/        | styles for webpages, copied from the PPL website|
| je_assets/     | see repository je_assets |
| js/            | only jquery is used|
| account.php    | webpage for changing registration |
| activation.php | webpage for activating a new registration (email verification) |
| index.php      | empty registration form |
| readme.md      | this file |
| sign-in.php    | sign in form |


## Pages
The following forms (pages) are defined:

* a registration form: index.php (new patrons)
* an activation page used for email verification after registration: activation.php
* a sign in form: sign-in.php (existing patrons)
* an account form: account.php (existing patrons)

These pages are copied from the PPL Website and adapted for the registration process.



