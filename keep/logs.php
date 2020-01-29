<?php
$path = 'domp_auto';
$links = array('jsonFiles','logs','pdf_domp','pdfs','tmp');
foreach($links as $link){
 $url = 'http://domp-aynoei177396.codeanyapp.com/'.$path.'/'.$link;
 echo '<a href="'.$url.'" target="_blank">'.$link.'</a></br>';
}
echo '<hr>';
echo '<a href="http://domp-aynoei177396.codeanyapp.com/domp_auto/command.php" target="_blank">1 - Command</a></br>';
echo '<a href="http://domp-aynoei177396.codeanyapp.com/domp_auto/domp_continuaPaser.php" target="_blank">2 - Domp Paser</a></br>';
echo '<a href="https://concursosmpgo.com.br/wp-json/questoes/v2/dompAuto" target="_blank">3 - Domp Auto</a></br>';
