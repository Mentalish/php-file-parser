<?php
include_once('log.php');
include_once('split.php');

function orchestrate($dataFile, $processCount, $destDirectory, $un, $pw, $host, $db) {
   $LOGFILE = 'import.log';
   splitFile($dataFile, $destDirectory, intval($processCount), 10);
   writeToLog($LOGFILE, "START", "Starting import procress on file" . $dataFile);

   $file_names = scandir($destDirectory);
   $lineOffset = 0;
   foreach ($file_names as $index => $file) {

      #skip dot directories
      if ($file == "." || $file == "..") {
         continue;
      }

      $filePath = $destDirectory . '/' . $file;
      writeToLog($LOGFILE, "PROCESS", "Initializing process number " . ($index - 2)); 
      shell_exec("php run.php " . $un . " " . $pw . " " . $host . " " . $db . " " . $filePath . " " . $lineOffset . " " . $LOGFILE . " > /dev/null 2>&1 &");
      $lineOffset += countFile($filePath);  
   }
}
