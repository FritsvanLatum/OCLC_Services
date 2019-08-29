<?php


//lees het eerste record
$marc_record = read_one_marc_record();   //<marc:record>
$record_number = content_of('<marc:controlfield tag="001">');

//while loop: ga door totdat er geen record meer is ingelezen
while (er is inderdaad een $marc_record gelezen) {              //check op eof
  if (dit is een BIB record) {
    //  converteren naar SOLR XML en
    //  opslaan in tabel BIB 
  }
  else if (dit is een LBD record) {
    //  converteren naar SOLR XML en
    //  opslaan in tabel LBD 
  }
  else if (dit is een LHR record) {
    //  converteren naar SOLR XML en
    //  opslaan in tabel LHR 
  }
  else {
    //er is iets mis
  }
  //lees het volgende record
  $marc_record = read_one_marc_record();
  $record_number = content_of('<marc:controlfield tag="001">');

}  //einde while loop


//  converteren naar SOLR XML en
//  opslaan in database


?>