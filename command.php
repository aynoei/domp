<?php
/*************************************************************DOMP_SEGUNDO**************************************************/
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

include dirname(__FILE__).'/class_pdf_domp.php';

$domp = new DOMP;
//$ordem = $_GET['domp'];
$path = 'domp_auto';
//$arquivo = file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/domp_auto/jsonFiles/proximo.json');
$url = 'http://'.$_SERVER['HTTP_HOST'].'/';
$arquivo = file_get_contents($url.$path.'/jsonFiles/proximo.json');
$arr = json_decode($arquivo);

$getordem = @$_GET['domp'];
$task = @$_GET['task'];

$ordem = (strlen($getordem) == 0)?$arr->proximo:$getordem;


if($arr->status == "1"){//<---------------------status
 $name_file = 'domp_'.$ordem.'.pdf';
 $link = dirname(__FILE__).'/pdf_domp/'.$name_file;
 $array = $domp->getData($ordem);
//$domp->deleteFiles();
/*
  echo '<pre>';
  var_dump($array);
  echo '</pre>';*/
  print_r(json_encode($array));
}else{
 
 if($task == 'refresh'){
  $domp->jsonFile('proximo',array('proximo'=>$ordem,'status'=>'1'));
  print_r(json_encode(array('erro'=>'Refresh realizado para o diario '.$ordem)));
 }else{
  $msg_e = array('erro'=>'Aguardando o paser do diario '.$ordem);
  print_r(json_encode($msg_e));  
  $domp->getError(json_encode(array('tipo'=>'command-page','diario'=>$ordem,'message'=>$msg_e)),'error','logs/error.log');
  }
}


 
