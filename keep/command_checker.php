<?php
/*************************************************************DOMP_SEGUNDO**************************************************/
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

include dirname(__FILE__).'/class_pdf_domp.php';

$domp = new DOMP;
//$ordem = $_GET['domp'];
$path = 'domp_auto';
//$arquivo = file_get_contents('http://domp-aynoei177396.codeanyapp.com/domp_auto/jsonFiles/proximo.json');
$url = 'http://domp-aynoei177396.codeanyapp.com/';
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
 ?>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<style type="text/css">
    #progress {
      width: 500px;
      border: 1px solid #aaa;
      height: 20px;
    }
    #progress .bar {
      background-color: #ccc;
      height: 20px;
    }
  </style>
<script type="text/javascript">
 
 
var paser = new EventSource('domp_checker_paser.php');
 
paser.addEventListener("paser", function(event){

       var d = JSON.parse(event.data);
       console.log(event.data);//get data
       console.log(d.content);
        console.log('ok');
 
      document.getElementById("progress").style.width =  d.content+'%';
      document.getElementById("conteudo").innerHTML = 'Processando Diário nº <?php echo $ordem; ?>'; 
 
        if (d.content == 100) {
             //checaProcesso('paser')
             document.getElementById("message").innerHTML = '<h4 class="alert-heading">Extração completa!</h4>';
             document.getElementById("message").className = 'alert alert-success';
             document.getElementById("progress").className = 'progress-bar bg-success progress';
          }

    
}, false);
 
 
 var extract = new EventSource('domp_checker_extract.php?ordem=<?php echo $ordem; ?>');
 
extract.addEventListener("extract", function(event){

       var d = JSON.parse(event.data);
       console.log(event.data);//get data
       console.log(d.content);

    
}, false);
  

    
</script>
 <div id="message" class="alert alert-warning" style="height: 86px;"><h4 class="alert-heading">Aguarde</h4><div id="conteudo"></div></div>
 <div class="progress">
  <div id="progress" class="progress-bar progress-bar-striped bg-success progress progress-bar-animated" role="progressbar" style="width: 0%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
 </div>
<?php
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


 
