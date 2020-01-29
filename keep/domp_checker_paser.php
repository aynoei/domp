<?php
include dirname (__DIR__) . '/vendor/autoload.php';

use Hhxsv5\SSE\SSE;
use Hhxsv5\SSE\Update;

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');//Nginx: unbuffered responses suitable for Comet and HTTP streaming applications

$file = $_GET['file'];


(new SSE())->start(new Update(function () {

$file_dir = dirname (__FILE__) . "/logs/paser.txt";//gera arquivo temporario para não conflitar com outros processos
$dados = file_get_contents($file_dir);
$d = explode('|',$dados);
$percent = $d[1] * 100;
$count =  $d[0];
$total = $d[2];
 
    $id = file_get_contents($file_dir);
    $newMsgs = [
            'id'      => $count,
            'title'   => $id,
            'content' => $percent,
            'total' => $total
    ];//get data from database or service.
 
  if($count == $total){
  
    unlink($file_dir);

  }
    if ($dados){
        return json_encode($newMsgs);
    }else{
       return false;//return false if no new messages
    }

}), 'paser');



?>