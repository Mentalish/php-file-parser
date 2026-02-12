<?php
include 'parse.php';
include 'lexer.php';

$un = "<user>";
$pw = "<password>";
$host = "<host>";
$db = "db";

$fileName = "equipment-part2.txt";
$dblink = new mysqli($host,$un,$pw,$db); //ODBC
$start = microtime(true);


$file = fopen($fileName, 'r');
$lineCount = 1;
while (!feof($file)) {
   $tokens = returnFileBuffer($file, 2048 * 7); 
   if($tokens) {
      parseTokens($tokens, 3, $lineCount, 'error', true, $dblink);
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
