<?php

$xml = file_get_contents("lookup_response.xml");



$xmlDoc = new DOMDocument();
$xmlDoc->preserveWhiteSpace = FALSE;
$xmlDoc->loadXML($xml);
$result = xml2json($xmlDoc);

/*
$roots = $xmlDoc->childNodes;
$json = array();

foreach ($roots as $root) {
$json[$xmlDoc->nodeName] = traverse($root,TRUE,TRUE);

$paths = getPaths($root);

file_put_contents("lookup_response_paths.php",'"'.implode("\",\n\"",$paths).'"');
}
*/


function xml2json($node,$remove_namespaces = TRUE,$remove_attributes = TRUE) {
  //echo '<pre>'.$node->nodeName." : ".$node->getNodePath()."</pre>\n";
  $result = array();
  if (($node->nodeType == XML_ELEMENT_NODE) || ($node->nodeType == XML_DOCUMENT_NODE)) {
    if ($node->hasChildNodes()) {
      foreach ($node->childNodes as $child) {
        if ($child->nodeType == XML_ELEMENT_NODE) {
          $ckey = $child->nodeName;
          if ($remove_namespaces) {
            $parts = explode(':',$ckey);
            if (count($parts) > 1) $ckey = $parts[1];
          }
          
          if ($remove_attributes) {
            $result[$ckey][] = xml2json($child);    
          }
/*          else {
              if ($node->hasAttributes()){
                foreach ($node->attributes as $attribute) {
                
                }
              }
              else {
                $result[$ckey][] = xml2json($child);
              }
              */
            }
            else if ($child->nodeType == XML_TEXT_NODE) $result[] = $child->textContent;
          }

          if (array_keys($result) === range(0, count($result) - 1)) {
            if (count($result) == 1) $result = $result[0];
          }
          else {
            foreach ($result as $k=>$v) {
              if (count($v) == 1) $result[$k] = $v[0];
            }
          }
        }
      }
      return $result;
    }



    /*
    function traverse($node,$remove_attr, $remove_namespaces) {


    if ($remove_attr) {
    if ($node->nodeType == XML_ELEMENT_NODE) {
    if ($node->hasChildNodes()) {
    foreach ($node->childNodes as $child) {
    $ckey = $child->nodeName;
    if ($remove_namespaces) {
    $parts = explode(':',$ckey);
    if (count($parts) > 1) $ckey = $parts[1];
    }
    if ($child->nodeType == XML_ELEMENT_NODE) {
    $child_json[$ckey][] = traverse($child,$remove_attr, $remove_namespaces);
    }
    else if ($child->nodeType == XML_TEXT_NODE)  $child_json[$ckey] = $child->textContent;
    }
    }
    }
    }
    else {

    if ($node->hasAttributes()){
    foreach ($node->attributes as $attribute) {
    $attr_key = $attribute->nodeName;
    $parts = explode(':',$attr_key);
    if (count($parts) > 1) $attr_key = $parts[1];

    $node_json[$key]['attr'][$attr_key] = $attribute->nodeValue;
    }
    }
    if ($node->nodeType == XML_ELEMENT_NODE) {
    if ($node->hasChildNodes()) {
    foreach ($node->childNodes as $child) {
    if ($child->nodeType == XML_ELEMENT_NODE) {
    $node_json[$key]['children'][] = traverse($child,$remove_attr, $remove_namespaces);
    }
    else if ($child->nodeType == XML_TEXT_NODE) $node_json[$key]['text'] = $child->textContent;
    }
    }
    }
    }
    //
    return $node_json;
    }
    */
    function getPaths($node) {
      $paths = array();
      $paths[] = $node->getNodePath();

      if ($node->nodeType == XML_ELEMENT_NODE) {
        if ($node->hasChildNodes()) {
          foreach ($node->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE)
            $paths = array_merge($paths,getPaths($child));
          }
        }
      }
      return $paths;

    }


    echo '<pre>'.json_encode($result,JSON_PRETTY_PRINT).'</pre>';
    echo "\n\n<pre>".json_encode($paths,JSON_PRETTY_PRINT).'</pre>';
    ?>