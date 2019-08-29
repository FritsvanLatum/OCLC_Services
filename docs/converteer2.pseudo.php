<?php


//lees het eerste record
$marc_record = read_one_marc_record();   //<marc:record>
$record_number = content_of('<marc:controlfield tag="001">');

//eerste while loop: ga door totdat er geen record meer is ingelezen
while (er is inderdaad een $marc_record gelezen) {              //check op eof
  //we hebben een BIB record
  //doe extra check of $record_number met 'on' begint?
  $ocn = $record_number;
  $marc_complete = '<doc><bib>'.$marc_record.'</bib>';

  //nu nog de LBD en LHR
  //lees het volgende record
  $marc_record = read_one_marc_record();   
  $record_number = content_of('<marc:controlfield tag="001">');
  
  //tweede while loop: lees de bijbehorende records in
  while ('<marc:controlfield tag="004">' aanwezig in dit $marc_record) {
    $part_of_ocn = content_of('<marc:controlfield tag="004">');
    if ($part_of_ocn == $ocn) {
      if (dit is een LBD record) {
        $marc_complete .= '<lbd>'.$marc_record.'</lbd>';
      }
      else if (dit is een LHR record) {
        $marc_complete .= '<lhr>'.$marc_record.'</lhr>';
      }
      else {
        //er is iets mis
      }
    }
    else {
      //er is iets mis
    }
    //lees het volgende record
    $marc_record = read_one_marc_record();   
    $record_number = content_of('<marc:controlfield tag="001">');

  }  //einde tweede while loop

  $marc_complete .= '</doc>';

  //alles is bij elkaar verzameld in $marc_complete, dus nu:
  //  converteren naar SOLR XML en
  //  opslaan in database

} //einde eerste while loop

?>