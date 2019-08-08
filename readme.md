# OCLC Services

This repository contains PHP libraries for communicating with OCLC's WMS.

OCLC offers several services. For each of these services there is a corresponding class.

## IDM 

The class IDM_Service, defined in IDM_Service.php contains the following functions 

| function |  description | 
|---|---| 
|read_patron_ppid | gets patron data with a given ppid | | 
|get_barcode |  gets the barcode from a patron that is already read from WMS | 
| | 
|read_patron_barcode |  gets patron data with a given barcode | 
|search_patron |  search a patron in WMS using a SCIM search string, used by read_patron_barcode and wms_barcode_exists | 
| | 
|wms_update |  generates SCIM json with changed user data, calls update_patron | 
|wms_activate |  generates SCIM json only to activate a user, calls update_patron | 
|update_patron |  updates a patron in WMS | 
| | 
|wms_new_barcode |  generates a new and not already existing barcode in WMS, uses new_barcode and wms_barcode_exists | 
|new_barcode |  generates a 10 digit barcode | 
|wms_barcode_exists |  checks whether a barcode already is used in WMS | 
| | 
|wms_create |  generates SCIM json with new user data, calls create_patron | 
|create_patron |  creates a patron in WMS | 

## NCIP

