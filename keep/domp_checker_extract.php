<?php
include dirname (__DIR__) . '/vendor/autoload.php';

use Hhxsv5\SSE\SSE;
use Hhxsv5\SSE\Update;

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');//Nginx: unbuffered responses suitable for Comet and HTTP streaming applications




(new SSE())->start(new Update(function () {
$ordem = $_GET['ordem'];
 
    $LogFim = @file_get_contents(dirname (__FILE__) . '/logs/domp_'.$ordem.'.log');
 
    if(strpos($LogFim,'saved')){
        //$output = shell_exec('php domp_continuaPaser.php > /dev/null & echo $!');
        return json_encode(array('extraido'.$ordem));
    }else{
       return json_encode(array('aguarde extrair diario '.$ordem));
    }

}), 'extract');



?>