<?php  
 $files = glob($_SERVER['DOCUMENT_ROOT'].'domp_auto/pdfs/*.{PDF,pdf}', GLOB_BRACE);
echo '<pre>';
var_dump($files);
echo '</pre>';