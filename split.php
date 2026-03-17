<?php
include_once 'log.php';

function splitFile($sourceFile, $destDirectory, $numFiles, $lineBufferSize, $logFileName) {
   $newFileName = "aaa";
   $fpointer = fopen($sourceFile, "r");
   $sourceLineCount = countFile($sourceFile);
   $lineBuffer = "";

   rewind($fpointer);

   $linesPerFile = $sourceLineCount / $numFiles;

   if(is_dir($destDirectory)) {
      shell_exec("rm -rf " . $destDirectory);
   }

   shell_exec("mkdir -p " . $destDirectory);
   
   for ($k=0; $k < $numFiles; $k++) {
      $linesProcessedInFragment = 0; 
      $fragmentFilePointer = fopen($destDirectory . "/" . $newFileName, "w");
      
      while ($linesProcessedInFragment < $linesPerFile && !feof($fpointer)) {
         $line = fgets($fpointer);
         
         checkIllegalDelimiterPlacement($line, $logFileName, $linesProcessedInFragment, $k, $linesPerFile);
        
         fwrite($fragmentFilePointer, $line);
         $linesProcessedInFragment++; 
      }
      
      fclose($fragmentFilePointer);
      $newFileName = str_increment($newFileName);
   }
   
   $linesProcessedInFragment = 0;
   if(!feof($fpointer)) {
      $lastFilePointer = fopen(str_decrement($newFileName), "a");
      while(!feof($fpointer)) {
         $line = fgets($fpointer);

         checkIllegalDelimiterPlacement($line, $logFileName, $linesProcessedInFragment, 5, $linesPerFile);

         fwrite($lastFilePointer, $line);
         $linesProcessedInFragment++;
      }
      fclose($lastFilePointer);
   } 
}

function countFile($sourceFile): int {
   $fpointer = fopen($sourceFile, "r");
   $lineCount = 0;
   while(fgets($fpointer)) {
      $lineCount++;
   }
   fclose($fpointer);
   return $lineCount;
}

function checkIllegalDelimiterPlacement (&$line, $logFileName, $linesProcessedInFragment, $k, $linesPerFile) {
   if(!is_string($line)) {
      return;
   }
    if($line[0] == ',') {
            writeToLog($logFileName, "FILE PREPROCESS", "illegal use of delimiter removed at beginning of line " . (($linesProcessedInFragment + 1) + ($k * $linesPerFile)));
            $line = substr_replace($line, '', 0, 1);
         }
   if($line[strlen($line) - 2] == ',') {
            writeToLog($logFileName, "FILE PREPROCESS", "illegal use of delimiter removed at end of line " . (($linesProcessedInFragment + 1) + ($k * $linesPerFile)));
            $line = substr_replace($line, '', -2, 1);
         }

}
?>
