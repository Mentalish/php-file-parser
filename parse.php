<?php
include_once('log.php');

function parseTokens(array $tokens, int $numParameters, int &$lineCount, string $errorLogName, bool $dbCopy, $dblink, &$deviceTypeCache, &$manufacturerCache): void {
   $TYPO_MANUFACTURER = "/(^[a-z])|([0-9@#$%^&*()'`])/";
   $TYPO_DEVICE_TYPE = "/([0-9@#$%^&*()'`])/";
   foreach ($tokens as $entry) {
      $errorLine = false;
      $lineCount++;
      //too many tokens in entry
      if($numParameters != count($entry)) {
         writeToLog($errorLogName, "DATA ERROR", "Too many or too few items in line: " . $lineCount);
         continue; 
      }

      $deviceType = $entry[0];
      $manufacturer = $entry[1];
      $serialNumber = $entry[2];
      $prefix = "";
      $body = "";

      if(detectEmpty($deviceType, "device type", $lineCount, $errorLogName)) {
         $errorLine = true;
      } else {
         if(checkTypo($TYPO_DEVICE_TYPE, $deviceType, "device type", $lineCount, $errorLogName)) {
         $errorLine = true;
         }
      }

      if(detectEmpty($manufacturer, "manufacturer", $lineCount, $errorLogName)) {
         $errorLine = true;
      } else {
         if(checkTypo($TYPO_MANUFACTURER, $manufacturer, "manufacturer", $lineCount, $errorLogName)) {
            $errorLine = true;
         }
      }

      if(detectEmpty($serialNumber, "serial number", $lineCount, $errorLogName)) {
         $errorLine = true;
      } else {
         if(validateSerialNumber($prefix, $body, $serialNumber, $lineCount, $errorLogName)) {
            $errorLine = true;
         }
      } 

      //write to db
      if ($errorLine == false && $dbCopy == true) {
         // find if manufacturer is already in the database if not create it
         if(!isset($manufacturerCache[$manufacturer])) {
            $sqlGet = "SELECT `manufacturer_id` FROM `manufacturers` WHERE `manufacturer_name` = '$manufacturer' ;"; 
            if(!($manufacturerId = $dblink->query($sqlGet)->fetch_column())) {
               $sqlInsert = "INSERT IGNORE INTO `manufacturers` (`manufacturer_name`) values ('$manufacturer')";  
               //if cant insert attempt to get manufacturer again
               if($dblink->query($sqlInsert) && $dblink->insert_id) {
                  $manufacturerId = $dblink->insert_id;
               } else { 
                  $manufacturerId = $dblink->query($sqlGet)->fetch_column();
               }
            }
            $manufacturerCache[$manufacturer] = $manufacturerId;
         } else {
            $manufacturerId = $manufacturerCache[$manufacturer]; 
         }

         // find if device type is already in the database if not create it
         if(!isset($deviceTypeCache[$deviceType])) {
            $sqlGet = "SELECT `device_type_id` FROM `device_types` WHERE `device_type_name` = '$deviceType' ;"; 
            if(!($deviceTypeId = $dblink->query($sqlGet)->fetch_column())) {
               $sqlInsert = "INSERT IGNORE INTO `device_types` (`device_type_name`) values ('$deviceType')";
               //if cant insert attempt to get manufacturer again
               if($dblink->query($sqlInsert) && $dblink->insert_id) {
                  $deviceTypeId = $dblink->insert_id;
               } else {
                  $deviceTypeId = $dblink->query($sqlGet)->fetch_column();
               }
            }
            $deviceTypeCache[$deviceType] = $deviceTypeId; 
         } else {
            $deviceTypeId = $deviceTypeCache[$deviceType];
         }
         
         //insert the entire entry to the main table
         $sql = "INSERT INTO `devices` (`device_type_id`, `manufacturer_id`, `serial_number_prefix`, `serial_number_body` , `line_number`)
           values ('$deviceTypeId', '$manufacturerId', '$prefix', '$body', '$lineCount')";
         $dblink->query($sql) or die;

         }  
      }
}

function partSerialNumber(string $fullSerialNumber, &$prefix, &$delimeter, &$body) : void {
   if($fullSerialNumber == null) {
      return;
   }

   $prefix = substr($fullSerialNumber, 0,2);
   $delimeter = $fullSerialNumber[2];
   $body = substr($fullSerialNumber, 3);
}

function detectEmpty($parameter, $parameterName, $lineNumber, $errorLogName) : bool {
   if($parameter == "") {
      writeToLog($errorLogName, "DATA ERROR", "Missing parameter: {" . $parameterName . "} at line: " . $lineNumber);
      return true;
   }

   return false;
}

function checkTypo($typoRegex, $parameter, $parameterName, $lineNumber, $errorLogName) : bool {
   if(strlen($parameter) == 1 || preg_match ($typoRegex, $parameter)) {
      writeToLog($errorLogName, "DATA ERROR", "Typo on parameter: " . $parameterName . " at line: " . $lineNumber);
      return true;
   }

   return false; 
}

function validateSerialNumber(&$prefex, &$body, $serialNumber, $lineNumber, $errorLogName) : bool {
      $delimeter = "";
      partSerialNumber($serialNumber, $prefix, $delimeter, $body);

      //incorect size
      if(strlen($body) != 32) {
         writeToLog($errorLogName, "DATA ERROR", "Serial number is the incorrect length on entry number " . $lineNumber);
         return true;
      }

      //incorect delimiter
      if($delimeter != '-') {
         writeToLog($errorLogName, "DATA ERROR (remediated)", "Incorrect delimiter found in serial number on entry number " . $lineNumber);
         return true;
      }

      return false;
}
?>
