# OCLC Services

This repository contains PHP libraries for communicating with OCLC's WMS. 
OCLC offers several API's. This repository contains a class for most of these API's.

## Base class OCLC_Service
This class, defined in OCLC_Service.php contains the knitty gritty of HMAC and token authorization. All classes 
described below are extensions of OCLC_Service.

For convenience this class contains the function `xml2json($node, $options) `

### parameters:
$node : must be a DOMNode (see PHP manual)

$options : associative array, with zero, one, two or all three of these elements:

Example with default values:
```
$options = [
'remove_namespaces' => TRUE,
'remove_attributes' => TRUE,
'remove_arrays_one_element' => FALSE,
]
```

### return value

An associative array.

### $options explained

#### remove_arrays_one_element:

With the default value of `remove_arrays_one_element` (FALSE) all values in the resulting associative array
are itself arrays. This is because this is valid XML:

```
...
<author>Jack</author>
<author>John</author>
...
```

But this is not allowed in an associative array:

```
[
...
'author' => 'Jack',
'author' => 'John',
...
]
```

Should be:

```
[
...
'author' => ['Jack', 'John'],
...
]
```

So each element becomes an array, also when there is only one value...
If `remove_arrays_one_element` is set to TRUE: always check whether a value is an array or a string

#### remove_attributes:
If `remove_attributes` is set to FALSE, then for each XML element an extra layer is added. The attribute key - value pairs are added and
the content of the XML element is added as:
`'_content_' => ...`

Example:

```
  <CirculationStatus Scheme="http://worldcat.org/ncip/schemes/v2/extensions/circulationstatus.scm">On Loan</CirculationStatus>
```
is converted to:

```
"CirculationStatus": [
  {
    "Scheme": "http:\/\/worldcat.org\/ncip\/schemes\/v2\/extensions\/circulationstatus.scm",
    "_content_": [
      "On Loan"
    ]
  }
],
```

#### remove_namespaces:
Set remove_namespaces to FALSE when there will be element name confusions otherwise.

 

## WorldShare Identity Management API

The class IDM_Service, defined in IDM_Service.php contains the following members and functions 

| member |  description | 
|---|---| 
| `patron` | holds the json representation of the response on a read request |
| `search` | holds the json representation of the response on a search request |
| `create` | holds the json representation of the response on a create request |
| `update` | holds the json representation of the response on a update request |


| function |  description | returns | 
|---|---|---| 
|`read_patron_ppid($id)` | gets patron data from WMS with a given ppid | TRUE/FALSE | 
|`get_barcode()` |  gets the barcode from a patron that is already read from WMS | barcode | 
| | 
|`read_patron_barcode($barcode)` |  gets patron data with a given barcode, uses `search_patron` |  TRUE/FALSE | 
|`search_patron($search)` |  search a patron in WMS using a SCIM search string, used by `read_patron_barcode` and `wms_barcode_exists` | TRUE/FALSE | 
| | 
|`wms_update($ppid, $barcode, $json` |  generates SCIM json with changed user data, calls `update_patron` |  ppid | 
|`wms_activate($ppid, $barcode, $json)` |  generates SCIM json only to activate a user, calls `update_patron` |  ppid | 
|`update_patron($ppid, $scim_json)` |  updates a patron in WMS |  TRUE/FALSE | 
| | 
|`wms_new_barcode($userName)` |  generates a new and not already existing barcode in WMS, uses `new_barcode` and `wms_barcode_exists` | barcode | 
|`new_barcode($userName)` |  generates a 10 digit barcode | barcode |
|`wms_barcode_exists($barcode)` |  checks whether a barcode already is used in WMS, uses `search_patron` |  TRUE/FALSE |  
| | 
|`wms_create($barcode,$patron_type,$json)` |  generates SCIM json with new user data, calls `create_patron` | ppid |
|`create_patron($scim_json)` |  creates a patron in WMS |  TRUE/FALSE | 

This class can be used to add and change user data in WMS.

### Dependencies
Install TWIG. Goto the directory with this repository and use the following command:

`composer require "twig/twig:^2.0"`

See https://twig.symfony.com

### TODO

add function wms_delete (not a real delete but a change of data like "isCircBlocked", "isVerified" and "oclcExpirationDate"

WorldShare Identity Management API at [OCLC website](https://www.oclc.org/developer/develop/web-services/worldshare-identity-management-api.en.html)

## WMS Availability API

The class Availability_Service, defined in Availability_Service.php contains the following members and functions 

| member |  description | 
|---|---| 
| `avail_xml` | holds the xml response of a get availability request |
| `avail` | holds the json representation of the response of a get availability request  |

| function |  description | returns | 
|---|---|---| 
|`get_availabilty_of_ocn($ocn)` | gets availability information based on an ocn | TRUE/FALSE | 
|`get_availabilty_query($query)` | gets availability information based on a query | TRUE/FALSE |
|`get_circulation_info($ocn)` | gives only the holding information from the availability information | array |

This class uses xml2json from OCLC_Service.php. The class can be used to chack "physical" availability of a publication.

Usage :
```
   require_once './Availability_Service.php';
   $avail = new Availability_Service('keys_availability.php');
   $holding = $avail->get_circulation_info($ocn)
   
   /*
   the complete response can be found in $avail->avail en in $avail->avail_xml
   */
```

WMS Availability API at [OCLC Website](https://www.oclc.org/developer/develop/web-services/wms-availability-api.en.html)

## WMS NCIP Service 

The class NCIP_Service, defined in NCIP_Service.php contains the following members and functions.

| member |  description | 
|---|---| 
| `patron_xml` | holds the xml response of a lookup patron request |
| `patron` | holds the json representation of the response of a lookup patron request  |
| `request_xml` | holds the xml response of a request request (place hold request)|
| `request` | holds the json representation of the response of a request request (place hold request) |
| `cancel_xml` | holds the xml response of a cancel request request |
| `cancel` | holds the json representation of the response of a cancel request request |

| function |  description | returns | 
|---|---|---| 
|`lookup_patron_ppid($ppid)` | gets the circulation information of a patron, uses the ppid of the patron | TRUE/FALSE | 
|`request_biblevel($ppid, $ocn)` | adds a hold to WMS of the patron on the publication with the ocn provided | TRUE/FALSE | 
|`cancel_request($ppid, $request_id` | cancels the request with the given requestId | TRUE/FALSE | 
|`renew_item_of_patron($ppid, $itemid)` |  | TRUE/FALSE | 
|`renew_all_items_of_patron($ppid)` |  | TRUE/FALSE | 

If a library uses automatic renewal, then the renew_ functions are not of much use. This class ony uses the so-called Patron Profile of the API.

The class can be used to check the circulation status of a user and to place and cancel hold requests.

WMS NCIP Service at [OCLC Website](https://www.oclc.org/developer/develop/web-services/wms-ncip-service.en.html). 


## WorldCat knowledge base API

The class WorldCat_KB_Service, defined in WorldCat_KB_Service.php contains the following members and functions. 

| member |  description | 
|---|---| 
| `kb_record` | holds the json representation of the response of a search request  |

| function |  description | returns | 
|---|---|---| 
|`search_kb_record($ocn)` | gets the knowledge base record with the given ocn | TRUE/FALSE | 
|`getlink($ocn,$type = 'via'))` | gets the link of the given ocn, type must be one of via, self, canonical, alternate | 

The default 'via' link gives the link via EZProxy. Just use:
```
  $href = getlink($ocn);
```
The 'canonical' link is the direct link to the publisher. The other two are internal links.

This class can be used to check whether online access to a publication exists.

Usage:
```
    require_once './WorldCat_KB_Service.php';
    $KB = new WorldCat_KB_Service('keys_worldcat_kb.php');
    $found = $KB->search_kb_record($ocn); //delete this line if only the link is needed
    $href = $KB->getlink($ocn);
    // complete response in $KB->kb_record
```

```
    require_once './WorldCat_KB_Service.php';
    $KB = new WorldCat_KB_Service('keys_worldcat_kb.php');
    
```

WorldCat knowledge base API at [OCLC Website](https://www.oclc.org/developer/develop/web-services/worldcat-knowledge-base-api.en.html). 

## WorldCat Discovery API
 
The class Dicovery_Service, defined in Dicovery_Service.php contains the following members and functions.

| member |  description | 
|---|---| 
| `read_headers` | holds an associative array with headers |
| `record_xml` | holds the xml response of a read record request |
| `record` | holds the json representation of the response of a read record request  |

| function |  description | returns | 
|---|---|---| 
|`wcds_read_record($ocn)` | gets the record with the given ocn | TRUE/FALSE | 
|`wcds_db_list()` | gets a list of database numbers and descriptions on which the institution is permitted to search | 

This API supports several Accept headers.

* application/rdf+xml
* text/plain
* text/turtle
* application/ld+json
* application/json (default in this class)

To change the response format:

```
$discovery = new Discovery_Service('some_keys.php');
$discovery->read_headers['Accept'] = 'text/turtle';
$discovery->wcds_read_record($ocn);

```
### TODO
Add and complete search functions. Improve `wcds_db_list`.

WorldCat Discovery API at [OCLC Website](https://www.oclc.org/developer/develop/web-services/worldcat-discovery-api.en.html).


