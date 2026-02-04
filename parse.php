<?php

function parseTokens(array $tokens, int $numParameters, int $lineCount, string $errorLogName): void {
   foreach ($tokens as $entry) {
      for ($i=0; $i < $numParameters; $i++) { 
         //empty parameter
         if($entry[i] == "") {
            $logfile = fopen($errorLogName, 'a');
            fwrite($logfile, "Missing parameter: " + i + " at line: " + $lineCount);
            fclose($logfile);
         }
      } 
      $lineCount++;
   }
}
