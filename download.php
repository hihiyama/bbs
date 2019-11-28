<?php

$filepath = 'images/background.jpg';
 
$filename = 'download.jpg';
 
header('Content-Type: application/force-download');
 
header('Content-Length: '.filesize($filepath));
 
header('Content-Disposition: attachment; filename="'.$filename.'"');
 
readfile($filepath);
?>