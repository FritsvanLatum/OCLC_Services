# OCLC Services: Export LHR's

This repository contains an application in which LHR's are downloaded from WMS for import in Syndeo.

## Dependencies
* [MarcEdit](https://marcedit.reeset.net/) must be installed.
* MarcEdit must be added to the Windows Path system variable.
* This app must be installed in a LAMP environment, i.e. [XAMP](https://www.apachefriends.org/index.html).
* [TWIG](https://twig.symfony.com) must be installed. Goto the directory with this repository and use the following command:


## Base class OCLC_Service
This class, defined in OCLC_Service.php contains the knitty gritty of HMAC and token 
authorization. All classes described below are extensions of OCLC_Service.


