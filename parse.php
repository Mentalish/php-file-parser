<?php
include 'log.php';

function parseTokens(array $tokens, int $numParameters, int &$lineCount, string $errorLogName, bool $dbCopy, $dblink, &$deviceTypeCache, &$manufacturerCache): void {
   $typo = '/[0-9@#$%^&*()\'`]|^[a-z]/';
   foreach ($tokens as $entry) {
      $errorLine = false;
      for ($i=0; $i < $numParameters; $i++) { 
         //empty parameter
         if($entry[$i] == "") {
            $errorLine = true;
            writeToLog($errorLogName, "DATA ERROR", "Missing parameter: " . $i . " at line: " . $lineCount);
            }

         //typo
         if($i != 3 && strlen($entry[$i]) == 1 | preg_match($typo, $entry[$i])) {
            writeToLog($errorLogName, "DATA ERROR", "Typo on parameter: " . $i . " at line: " . $lineCount);

         }
         
         //too many tokens in entry
         if($numParameters != count($entry)) {
            $errorLine = true;
            writeToLog($errorLogName, "DATA ERROR", "Too many or too few items in entry");
         }

      }

      //serial number error
      $prefix = "";
      $delimeter = "";
      $body = "";
      $serialNumberFull = partSerialNumber($entry[2], $prefix, $delimeter, $body);

      //incorect size
      if(strlen($body) != 32) {
         $errorLine = true;
         writeToLog($errorLogName, "DATA ERROR", "Serial number is the incorrect length on entry number " . $lineCount);
      }

      //incorect delimiter
      if($delimeter != '-') {
         writeToLog($errorLogName, "DATA ERROR (remediated)", "Incorrect delimiter found in serial number on entry number " . $lineCount);
      }
      
      //write to db
      if ($errorLine == false && $dbCopy == true) {
         // find if manufacturer is already in the database if not create it
         if(!isset($manufacturerCache[$entry[1]])) {
            $sql = "SELECT `manufacturer_id` FROM `manufacturers` WHERE `manufacturer_name` = '$entry[1]' ;"; 
            if(!($manufacturerId = $dblink->query($sql)->fetch_column())) {
               $sql = "INSERT INTO `manufacturers` (`manufacturer_name`) values ('$entry[1]')";
               $dblink->query($sql);
               $manufacturerId = $dblink->insert_id;
            }
            $manufacturerCache[$entry[1]] = $manufacturerId;
         } else {
            $manufacturerId = $manufacturerCache[$entry[1]]; 
         }

         // find if device type is already in the database if not create it
         if(!isset($deviceTypeCache[$entry[0]])) {
            $sql = "SELECT `device_type_id` FROM `device_types` WHERE `device_type_name` = '$entry[0]' ;"; 
            if(!($deviceTypeId = $dblink->query($sql)->fetch_column())) {
               $sql = "INSERT INTO `device_types` (`device_type_name`) values ('$entry[0]')";
               $dblink->query($sql);
               $deviceTypeId = $dblink->insert_id;
            }
            $deviceTypeCache[$entry[0]] = $deviceTypeId; 
         } else {
            $deviceTypeId = $deviceTypeCache[$entry[0]];
         }
         
         //insert the entire entry to the main table
         $sql = "INSERT INTO `devices` (`device_type_id`, `manufacturer_id`, `serial_number_prefix`, `serial_number_body` , `line_number`)
           values ('$deviceTypeId', '$manufacturerId', '$prefix', '$body', '$lineCount')";
         $dblink->query($sql) or die;

      }  
      $lineCount++;
      }
}

function partSerialNumber(string $fullSerialNumber, &$prefix, &$delimeter, &$body) : void {
   $prefix = substr($fullSerialNumber, 0,2);
   $delimeter = $fullSerialNumber[2];
   $body = substr($fullSerialNumber, 3);
}
?>
