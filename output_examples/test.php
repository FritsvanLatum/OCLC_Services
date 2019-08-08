<?php
$xml=simplexml_load_file("test_NCIP_lookup_response.xml");
print($xml->__toString());
echo json_encode($xml,JSON_PRETTY_PRINT);
?> 