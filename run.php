<?php
include 'parse.php';
include 'lexer.php';

$un = $argv[1];
$pw = $argv[2];
$host = $argv[3];
$db = $argv[4];

$fileName = $argv[5];
$dblink = new mysqli($host,$un,$pw,$db); //ODBC
$start = microtime(true);

$manufacturerCache = [];
$deviceTypeCache = [];

$file = fopen($fileName, 'r');
$lineNumber = $argv[6] + 1;
while (!feof($file)) {
   $tokens = returnFileBuffer($file, 2048 * 7); 
   if($tokens) {
      parseTokens($tokens, 3, $lineNumber, 'error', true, $dblink, $deviceTypeCache, $manufacturerCache);
   }else {
      echo "no tokens";
   }
}

$end = microtime(true);
fclose($file);
$timeSeconds = $end - $start;
$timeMin = $timeSeconds/60;
echo "Complete";
echo "Time Seconds: $timeSeconds";
echo "Time Minutes: $timeMin";
?>
