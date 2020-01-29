<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

include 'class_pdf_domp.php';

$domp = new DOMP;
$ordem = $_GET['domp'];

 $name_file = 'domp_'.$ordem.'.pdf';
 $link = dirname(__FILE__).'/pdf_domp/'.$name_file;

$array_linhas_id = array('02410894178','3684','9401',0000,'7686','9954');

$array = $domp->pdfsDomp($ordem);
//$domp->deleteFiles();
/*
  echo '<pre>';
  var_dump($array);
  echo '</pre>';*/

  print_r(json_encode());