<?php
function splitFile($sourceFile, $destDirectory, $numFiles, $lineBufferSize) {
   $newFileName = "aaa";
   $fpointer = fopen($sourceFile, "r");
   $sourceLineCount = countFile($fpointer);

   rewind($fpointer);

   $linesPerFile = $sourceLineCount / $numFiles;

   if(is_dir($destDirectory)) {
      rmdir($destDirectory);
   }

   mkdir($destDirectory);
   
   for ($k=0; $k < $numFiles; $k++) { 
      $fragmentFilePointer = fopen($destDirectory, "/" . $newFileName, "w");
      for ($i=0; $i < $linesPerFile ; $i++) { 
         for ($j=0; $j < $lineBufferSize; $j++) { 
            $lineBuffer = fgets($sourceFile);
         }
         fwrite($fragmentFilePointer, $lineBuffer);
      }

      if($k == $numFiles - 1) {
         while(!feof($sourceFile)) {
            for ($l=0; $l < $lineBufferSize; $l++) { 
            $lineBuffer = fgets($sourceFile);
            }
         fwrite($fragmentFilePointer, $lineBuffer);
         }
      }

      if($lineBuffer) {
         fwrite($fragmentFilePointer, $lineBuffer); //handle partial buffer
      }

      fclose($fragmentFilePointer);
   } 
}

function countFile($sourceFile): int {
   $lineCount = 0;
   while(fgets($sourceFile)) {
      $lineCount++;
   }
   return $lineCount;
}
?>
