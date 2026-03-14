<?php
function splitFile($sourceFile, $destDirectory, $numFiles, $lineBufferSize) {
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
      
      while ($linesProcessedInFragment <= $linesPerFile && !feof($fpointer)) {
         fwrite($fragmentFilePointer, fgetc($fpointer));
         $linesProcessedInFragment++; 
      }
      
      fclose($fragmentFilePointer);
      str_increment($newFileName);
   }

   if(!feof($fpointer)) {
      $lastFilePointer = fopen(str_decrement($newFileName), "w");
      fwrite($lastFilePointer, fgetc($fpointer));
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
?>
