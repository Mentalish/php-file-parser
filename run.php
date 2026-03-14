<?php
include_once('parse.php');
include_once('lexer.php');
include_once('log.php');

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
$lineNumber = $argv[6];
$processNumber = $argv[7];
$fileEntriesCount = $argv[8];
while ($tokens = getEntries($file, 10)) {
   parseTokens($tokens, 3, $lineNumber, $argv[7], true, $dblink, $deviceTypeCache, $manufacturerCache);
}

$end = microtime(true);
fclose($file);
$timeSeconds = $end - $start;
$timeMin = $timeSeconds/60;
$filesPerSec = $fileEntriesCount / $timeSeconds;
writeToLog($processNumber, "PROCESS", "Process finished processing file: Time elapsed " . $timeMin . ";Records Processed: " . $fileEntriesCount . " ;Files Per Second: " . $filesPerSec);
?>
