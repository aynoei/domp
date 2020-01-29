<?php
/*************************************************************DOMP_SEGUNDO**************************************************/
//sleep(120);//2 minutos
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

include 'class_pdf_domp.php';

$path = 'domp_auto';
$url = 'http://'.$_SERVER['HTTP_HOST'].'/';
$domp = new DOMP;

$dir = dirname(__FILE__).'/pdf_domp/';
$getordem = @$_GET['domp'];
$empty = (count(glob("$dir/*")) === 0) ? false : true;
/*********************************************************/
$arquivo = file_get_contents($url.$path.'/jsonFiles/proximo.json');
$arr = json_decode($arquivo);
$ordem = (strlen($getordem)>0)?$getordem:$arr->proximo;

$LogFim = @file_get_contents($url.$path.'/logs/domp_'.$ordem.'.log');
/********************************************************/

if(strpos($LogFim,'saved')){//se finalizado a funcao command
   if($empty){
           $files = glob($dir."*.pdf");
           $name_file = basename($files[0]);
           $link = $files[0]; 
           $output = dirname(__FILE__).'/pdfs';

     $dia = explode(".",$name_file);//$d = domp_200.pdf | ficando domp_200 e pdf
     $ario = explode("_",$dia[0]);//domp e 200
     $diario = $ario[1];//200

            $return = array( 
             'link'=> $link, 
             'nome'=> $name_file,
             'diario'=> $diario,
             'status'=>200,
             'url'=>'http://www.mpgo.mp.br/portal/domp/edicao/'.$diario,
             'message'=>$empty
             );

   $array = $domp->processarDados($return);
   }else{
    $array = array('task'=>'domp_continuaPaser','error'=>'Não há arquivos na pasta para processar!','message'=>$return);
    $domp->getError(json_encode($array),'error','/logs/error.log'); 
   }
}else{
 $array = array('task'=>'domp_continuaPaser','error'=>'Aguardando - '.$ordem);
 $domp->getError(json_encode($array),'error','/logs/error.log'); 
}

//$array = $domp->parserPdfs($ordem);

/*
  echo '<pre>';
  var_dump($array);
  echo '</pre>';*/

  print_r(json_encode($array));
