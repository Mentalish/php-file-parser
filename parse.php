<?php

function parseTokens(array $tokens, int $numParameters, int &$lineCount, string $errorLogName, bool $db_copy, $dblink): void {
      foreach ($tokens as $entry) {
      $errorLine = false;
      for ($i=0; $i < $numParameters; $i++) { 
         //empty parameter
         if($entry[$i] == "") {
            $errorLine = true;
            $logfile = fopen($errorLogName, 'a');
            fwrite($logfile, "Missing parameter: " . $i . " at line: " . $lineCount . "\n");
            fclose($logfile);
         }
      }

      if ($errorLine == false && db == true) {
         $sql="Insert Into 'devices' ('device_type', 'manufacturer_type', 'serial_number')
           values ('$entry[0]', $entry[1], $entry[2])";
   $dblink->query($sql) or
           die("Something went wrong with $sql<br>".$dblink->error); 
      }  
      $lineCount++;
   }
}
?>
