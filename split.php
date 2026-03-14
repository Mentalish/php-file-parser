<?php
function splitFile($sourceFile, $destDirectory, $numFiles, $lineBufferSize) {
   $newFileName = "aaa";
   $fpointer = fopen($sourceFile, "r");
   $sourceLineCount = countFile($sourceFile);

   rewind($fpointer);

   $linesPerFile = $sourceLineCount / $numFiles;

   if(is_dir($destDirectory)) {
      shell_exec("rm -rf " . $destDirectory);
   }

   shell_exec("mkdir -p " . $destDirectory);
   
   for ($k=0; $k < $numFiles; $k++) { 
      $fragmentFilePointer = fopen($destDirectory . "/" . $newFileName, "w");
      for ($i=0; $i < $linesPerFile ; $i++) { 
         for ($j=0; $j < $lineBufferSize; $j++) { 
            $lineBuffer = fgets($fpointer);
         }
         fwrite($fragmentFilePointer, $lineBuffer);
      }

      if($k == $numFiles - 1) {
         while(!feof($sourceFile)) {
            for ($l=0; $l < $lineBufferSize; $l++) { 
            $lineBuffer = fgets($fpointer);
            }
         fwrite($fragmentFilePointer, $lineBuffer);
         }
      }

      if($lineBuffer) {
         fwrite($fragmentFilePointer, $lineBuffer); //handle partial buffer
      }

      fclose($fragmentFilePointer);
      str_increment($newFileName);
   } 
}

function countFile($sourceFile): int {
   $fpointer = fopen($sourceFile, "r");
   $lineCount = 0;
   while(fgets($fpointer)) {
      $lineCount++;
   }
   return $lineCount;
}
?>
