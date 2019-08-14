<?php




function xml2json($node,$options) {
  //echo '<pre>'.$node->nodeName." : ".$node->getNodePath()."</pre>\n";
  $remove_namespaces = array_key_exists('remove_namespaces',$options) ? $options['remove_namespaces'] : TRUE;
  $remove_attributes = array_key_exists('remove_attributes',$options) ? $options['remove_attributes'] : TRUE;
  $remove_arrays_one_element = array_key_exists('remove_arrays_one_element',$options) ? $options['remove_arrays_one_element'] : FALSE;
  $options = [
  'remove_namespaces' => $remove_namespaces,
  'remove_attributes' => $remove_attributes,
  'remove_arrays_one_element' => $remove_arrays_one_element,
  ];
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
            $result[$ckey][] = xml2json($child,$options);
          }
          else {
            $sub = array();
            if ($child->hasAttributes()){
              foreach ($child->attributes as $attribute) {
                $attr_key = $attribute->nodeName;
                if ($remove_namespaces) {
                  $parts = explode(':',$attr_key);
                  if (count($parts) > 1) $attr_key = $parts[1];
                }
                $sub[$attr_key] = $attribute->nodeValue;
              }
            }
            $sub['_content_'] =  xml2json($child,$options);

            $result[$ckey][] = $sub;
          }
        }
        else if ($child->nodeType == XML_TEXT_NODE) $result[] = $child->textContent;
      }
        if ($remove_arrays_one_element) {

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
  }
  return $result;
}


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

$xml = file_get_contents("lookup_response.xml");
$xmlDoc = new DOMDocument();
$xmlDoc->preserveWhiteSpace = FALSE;
$xmlDoc->loadXML($xml);


echo "\nDefault\n";
echo '<pre>'.json_encode(xml2json($xmlDoc, []),JSON_PRETTY_PRINT).'</pre>';
echo "\nremove_namespaces FALSE\n";
echo '<pre>'.json_encode(xml2json($xmlDoc, ['remove_namespaces' => FALSE]),JSON_PRETTY_PRINT).'</pre>';
echo "\nremove_arrays_one_element TRUE\n";
echo '<pre>'.json_encode(xml2json($xmlDoc, ['remove_arrays_one_element' => TRUE]),JSON_PRETTY_PRINT).'</pre>';
echo "\nremove_attributes FALSE\n";
echo '<pre>'.json_encode(xml2json($xmlDoc, ['remove_attributes' => FALSE]),JSON_PRETTY_PRINT).'</pre>';


//echo "\n\n<pre>".json_encode($paths,JSON_PRETTY_PRINT).'</pre>';
?>