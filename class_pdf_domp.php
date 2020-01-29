<?php
/*************************************************************DOMP_SEGUNDO**************************************************/
/*
Classe de manipulação dos PDF Portifolios das publicações do DOMP, DIário Oficial do Ministério Público
Usa as classes do PDFTK Server e do PDF2TEXT, entre outras bibliotecas
Fluxo: 
1-Apaga qualquer arquivo da pasta pdf_domp(repositorio) e depois baixa o PDF do link do MPGO - getData();--chamada unica
2-Pull de Funções -- chamada unica
2.1 - Busca na pasta pdf_domp o unico arquivo baixado e extrai todos os pdf dele salvando na pasta pdfs - command();
2.2 - Faz loop nos pdfs da pasta pdfs e retira o texto deles - parserPdfs();
2.3 - Pega os meta dados do portfolio - portfolioData();
2.4 - Apaga todos os arquivos das pastas pdf_domp e pdfs - deleteFiles();
2.5 - Salva logs dos error e sucessos do processo - getError(); jsonFile();
2.6 - gera uma array com os dados coletados e salva um arquivo json - jsonFile();
2.7 - altera o diario a ser baixado para a proxima vez, guia de dounload, salvando no arquivo proximo.json - jsonFile();

Os Fluxos 1 e 2 são chamados em cron para evitar o erro 504, sendo que o 2 tem delay de 120 segundos para iniciar, evitando o inicio simultaneo

*/
include dirname (__DIR__) . '/vendor/autoload.php';
//require_once dirname (__DIR__) . '/libs/URLify.php';

use mikehaertl\shellcommand\Command;
use mikehaertl\pdftk\Pdf;
$pdf2text = new Spatie\PdfToText\Pdf;

$pdfParser = new \Smalot\PdfParser\Parser();




class DOMP{ 
 
public function getData($diario){
  
  self::deleteFiles();
  $file_source = 'http://www.mpgo.mp.br/portal/domp/edicao/'.$diario;
  $code = self::remote_file_exists($file_source);
  
  if($code){//se o link não existir
  
        $name_file = 'domp_'.$diario.'.pdf';
        $link = '/pdf_domp/'.$name_file; 
        $output = dirname(__FILE__).'/pdfs';
  $script = 'continuaPaser.php'; 
  $download_file = 'wget -bc '.$file_source.' -O '.dirname(__FILE__).$link.' -o '.dirname(__FILE__).'/logs/domp_'.$diario.'.log';
  $unpack_portfolio = '/usr/bin/pdftk '.$link.' unpack_files output '.$output;
  $php_script = 'domp_continuaPaser.php';
  $command_total = $download_file.' wait && php '.$php_script;
  $command = new Command($download_file); //separa os pdfs 
  if ($command->execute()) {
         $return = array( 
          'link'=> $link, 
          'nome'=> $name_file,
          'diario'=> $diario,
          'status'=>200,
          'url'=>'http://www.mpgo.mp.br/portal/domp/edicao/'.$diario,
          'success'=>$command->getOutput(),
          'message'=>$command->getStdErr()
          );         
        
         self::getError(json_encode(array('tipo'=>'download','diario'=>'domp_'.$diario,'message'=>$return)));
         self::jsonFile('proximo',array('proximo'=>$diario,'status'=>'2'));
        //self::processarDados($return);
 
        
     } else {
         $return = 'error_command => '.$command->getError().'---'.$command->getExitCode();
        self::getError(json_encode(array('tipo'=>'download','diario'=>$diario,'message'=>$return)),'error','logs/error.log'); 
     }
  }else{
    $return = array('error'=>'Url nao existe!');
    self::getError(json_encode(array('tipo'=>'url','diario'=>$diario,'message'=>$return)),'error','logs/error.log'); 
    self::jsonFile('proximo',array('proximo'=>$diario,'status'=>'1'));
  }
  return $return;
 }
/***************************************************Carregado pelo script domp_continuarPaser******************/ 
public function processarDados($link){//principal construct
 
 $name_file = $link['nome'];
 $tamanho = filesize($link['link']); 
 
 if(!empty($link)){
       $com = self::commands(dirname(__FILE__).'/pdf_domp/'.$link['nome']);
      self::getError(json_encode(array('tipo'=>'command-link','diario'=>$link['diario'],'message'=>$com)),'info','logs/processo_fase.log');
   }else{ self::getError(json_encode(array('tipo'=>'link','diario'=>$link['diario'],'message'=>$link)),'error','logs/error.log');}
 if(!empty($com)){//      
      $parser = self::parserPdfs($link['diario']);
      self::getError(json_encode(array('tipo'=>'parser','diario'=>$link['diario'],'message'=>'ok')),'info','logs/processo_fase.log');
    }else{ self::getError(json_encode(array('tipo'=>'command-com','diario'=>$link['diario'],'message'=>$com)),'error','logs/error.log');}
    $portfolio_data = self::portfolioData($name_file); 
   if(!empty($parser)){//apaga os arquivos gerados das pastas      
    $file_log = dirname(__FILE__).'/logs/domp_'.$link['diario'].'.log';
     if(is_file($file_log)){ unlink($file_log); }
   }else{ self::getError(json_encode(array('tipo'=>'paser','diario'=>$link['diario'],'message'=>$parser)),'error','logs/error.log');}//pega o erro no paser   
   /************************************************************/
if(!empty($parser)){
   self::deleteFiles();
    $error = array('tipo'=>'extrair','diario'=>$link['nome'],'link'=>$link['url'],'size'=>$tamanho,'respostas'=>array('Commands'=>$com,'Parser'=>'ok','Portfolio'=>$portfolio_data),
               );
    self::getError(json_encode($error)); 
 
    $result = array('tipo'=>$link['status'],'link'=>$link['url'],'size'=>$tamanho,'portfolio_link'=>$link,'portfolio_data'=>$portfolio_data,'conteudos'=>$parser);
   
   $proximo = $link['diario'] + 1;
   self::jsonFile('proximo',array('proximo'=>$proximo,'status'=>'1'));//salva um json com o proximo arquivo  
   self::jsonFile('json_'.$link['diario'],$result);//salva um json com os dados obtidos
   return $result;
}else{
     self::getError(json_encode(array('tipo'=>'processarDados','diario'=>$link['diario'],'message'=>$parser)),'error','logs/error.log');
     self::jsonFile('proximo',array('proximo'=>$link['diario'],'status'=>'1')); //se houver erro, volta ao mesmo diario    
     return array('erro_processar_dados');
}
}
 
public function commands($link){//executa o comando para extrair os arquivos
  
  $command = new Command('/usr/bin/pdftk '.$link.' unpack_files output '.dirname(__FILE__).'/pdfs');
     if ($command->execute()) {
         $return = 'success: '.$command->getOutput().'message: '.$command->getStdErr();
     } else {
      $return = 'error: '.$command->getError().$command->getExitCode();
         self::getError(json_encode(array('tipo'=>'command_error','diario'=>$link,'mensagem'=>$return)),'error','logs/error_naofeitos.log');
         
         
     }
  
  return $return;
  
 }

 public function portfolioData($file){//pega os dados do portfolio
  global $pdf2text;
   $pdf = new Pdf(dirname(__FILE__).'/pdf_domp/'.$file);
   return $pdf->getData();
  
 }
 
public function parserPdfs($d){//transforma em texto o pdf
  
  global $pdf2text;
  
  $files = glob(dirname(__FILE__).'/pdfs/*.{pdf,PDF}', GLOB_BRACE);
  $text;
  $file_dir = dirname (__FILE__) . "/logs/paser.txt";
  $contagem = 1;
  $total = count($files);
  foreach($files as $file) {
   $cont = $contagem++;
     $texto = $pdf2text->getText($file);
     $vazios[] = $texto;
     $text[] = array('arquivo'=>basename($file),'texto'=>$texto);
     file_put_contents($file_dir, $cont.'|'.($cont/$total).'|'.$total);
  }
 
       if(!empty($vazios)){
        $return = $text;
        self::getError(json_encode(array('tipo'=>'parserCount','diario'=>$d,'total'=>count($text))),'info','logs/tarefas.log');
       }else{
        $return = array();
        self::getError(json_encode(array('tipo'=>'function_parser','diario'=>$d,'message'=>'sem pdfs para phaser')),'info','logs/processo_fase.log');
       }
  return $text;
 }
 
 public function deleteFiles($file=''){
  $files = glob(dirname(__FILE__).'/pdfs/*.{pdf,PDF}', GLOB_BRACE);// get all file names
     foreach($files as $file){ // iterate files
       if(is_file($file))
         unlink($file); // delete file
     }
  $files = glob(dirname(__FILE__).'/pdf_domp/*.{pdf,PDF}', GLOB_BRACE);// get all file names
     foreach($files as $file){ // iterate files
       if(is_file($file))
         unlink($file); // delete file
     }
 }
 
 public function remote_file_exists($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if( $httpCode == 200 ){return true;}
    return false;
}
 
public function jsonFile($nome,$dados){      
        $response = $dados;
        $fp = fopen(dirname(__FILE__).'/jsonFiles/'.$nome.'.json', 'w');
       // fwrite($fp, json_encode($response));
        fwrite($fp, json_encode($response, JSON_UNESCAPED_UNICODE));       
 fclose($fp);
}
 
public function getError( $msg, $level = 'info', $file = 'logs/processo.log' ){
    // variável que vai armazenar o nível do log (INFO, WARNING ou ERROR)
    $levelStr = '';

    // verifica o nível do log
    switch ( $level )
    {
        case 'info':
            // nível de informação
            $levelStr = 'INFO';
            break;

        case 'warning':
            // nível de aviso
            $levelStr = 'WARNING';
            break;

        case 'error':
            // nível de erro
            $levelStr = 'ERROR';
            break;
    }

    // data atual
    $date = date( 'Y-m-d H:i:s' );

    // formata a mensagem do log
    // 1o: data atual
    // 2o: nível da mensagem (INFO, WARNING ou ERROR)
    // 3o: a mensagem propriamente dita
    // 4o: uma quebra de linha
    $msg = sprintf( "[%s] [%s]: %s%s", $date, $levelStr, $msg, PHP_EOL );

    // escreve o log no arquivo
    // é necessário usar FILE_APPEND para que a mensagem seja escrita no final do arquivo, preservando o conteúdo antigo do arquivo
   return file_put_contents( dirname(__FILE__).'/'.$file, $msg, FILE_APPEND );
}
 
 

}
