<?php
include 'parse.php';
include 'lexer.php';

$file = fopen('data/equipment-part3.txt', 'r');
$lineCount = 1;
while (!feof($file)) {
   $tokens = returnFileBuffer($file, 2048 * 7); 
   if($tokens) {
      parseTokens($tokens, 3, $lineCount, 'logs/error');
   }else {
      echo "no tokens";
   }
}   


