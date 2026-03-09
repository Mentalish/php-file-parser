<?php

function parseTokens(array $tokens, int $numParameters, int &$lineCount, string $errorLogName, bool $dbCopy, $dblink): void {
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

         //typo
         
         //too many tokens in entry
         if($numParameters != count($entry)) {
         
         }

      }

      //serial number error
      $serialNumber = partSerialNumber($entry[2], $prefix, $delimeter, $body);

      //incorect size
      if(count($body) != 32) {
      
      }

      //incorect delimiter
      if($delimeter != '-') {
      
      }
      
      //write to db
      if ($errorLine == false && $dbCopy == true) {
         // find if manufacturer is already in the database if not create it
         $sql = "SELECT `manufacturer_id` FROM `manufacturers` WHERE `manufacturer_name` = \"" . $entry[1] . "\";"; 
         if(!($manufacturerId = $dblink->query($sql))) {

         }

         // find if device type is already in the database if not create it
         $sql = "SELECT `device_type_id` FROM `device_types` WHERE `device_type_name` = \"" . $entry[0] . "\";"; 
         if(!($deviceTypeId = $dblink->query($sql))) {

         }

         //create new serial number
         $sql = "SELECT `serial_number_id` FROM `serial_numbers` WHERE `prefix` = \"" . $prefix . "\" AND \""  . "`body` = \"" . $body . "\";"; 
         if(!($serialNumberId = $dblink->query($sql))) {

         }

         //insert the entire entry to the main table
         $sql = "INSERT INTO `devices` (`device_type_id`, `manufacturer_id`, `serial_number_id`, 'line_numer')
           values ('$deviceTypeId', '$manufacturerId', '$serialNumberId', '$lineCount')";
         $dblink->query($sql) or die;

      }  
      $lineCount++;
      }
}

function partSerialNumber(string $fullSerialNumber, &$prefix, &$delimeter, &$body) : void {
   $prefix = array_slice($fullSerialNumber, 0,2);
   $delimeter = $fullSerialNumber[2];
   $body = array_slice($fullSerialNumber, 3);
}
?>
