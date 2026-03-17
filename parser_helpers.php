<?php
include_once 'log.php';
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

function checkAndFixTypo($typoRegex, &$parameter, $parameterName, $lineNumber, $errorLogName) : bool {
   if(strlen($parameter) == 1) {
      writeToLog($errorLogName, "DATA ERROR", "Paramter too short: " . $parameterName . " at line: " . $lineNumber);
      return true;
   }

   $newParameter = preg_replace($typoRegex, '', $parameter);
   if($newParameter !== $parameter) {
      writeToLog($errorLogName, "DATA ERROR (REMIDIATED)", "Typo found and fixed at " . $lineNumber . "; new string: " . $newParameter . "; old string: " . $parameter);
      $parameter = $newParameter;
      return false;
   }
   return false; 
}

function checkAndFixCase(&$parameter, $paramterName, $lineNumber, $errorLogName){
   if(preg_match('/^[a-z]/', $parameter)) {
      $newParameter = ucfirst($parameter);
      writeToLog($errorLogName, "DATA ERROR (REMIDIATED)", "Typo (CASE) found and fixed at " . $lineNumber . "; new string: " . $newParameter . "; old string: " . $parameter);
   }
}

function validateSerialNumber(&$prefix, &$body, $serialNumber, $lineNumber, $errorLogName) : bool {
      $delimeter = "";
      partSerialNumber($serialNumber, $prefix, $delimeter, $body);

      //incorect size
      if(strlen($body) != 64) {
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

function writeDeviceType($errorLogFile, $dblink, &$deviceTypeCache, $deviceType, &$deviceTypeId, $entryNumber, &$joeyWordCache) {
// find if device type is already in the database if not create it
   if(!isset($deviceTypeCache[$deviceType])) { //cache miss
      $sqlGet = "SELECT `device_type_id` FROM `device_types` WHERE `device_type_name` = '$deviceType' ;"; 
      if(!($deviceTypeId = $dblink->query($sqlGet)->fetch_column())) { //db miss
         if(!isset($joeyWordCache[$deviceType])) { //joey word cache miss
            $possibleJoey = $deviceType;
            $isJoey = checkSimilarity($errorLogFile, array_keys($deviceTypeCache), $deviceType, $entryNumber);
            $sqlInsert = "INSERT IGNORE INTO `device_types` (`device_type_name`) values ('$deviceType')";
            $sqlGet = "SELECT `device_type_id` FROM `device_types` WHERE `device_type_name` = '$deviceType' ;"; //refesh with new devicetype 
            //if cant insert attempt to get manufacturer again
            if($dblink->query($sqlInsert) && $dblink->insert_id) {
               $deviceTypeId = $dblink->insert_id;
            } else {
               $deviceTypeId = $dblink->query($sqlGet)->fetch_column();
            }
            if ($isJoey) { // new joey    
               $joeyWordCache[$possibleJoey] = $deviceTypeId; 
            }
         } else {
            $deviceTypeId = $joeyWordCache[$deviceType]; 
         }
      }
      $deviceTypeCache[$deviceType] = $deviceTypeId; 
   } else { // cache hit
      $deviceTypeId = $deviceTypeCache[$deviceType];
   }
}

function writeManufacturer($errorLogFile, $dblink, &$manufacturerCache, $manufacturer, &$manufacturerId, $entryNumber, &$joeyWordCache) {
// find if manufacturer is already in the database if not create it
   if(!isset($manufacturerCache[$manufacturer])) { //cache miss
      $sqlGet = "SELECT `manufacturer_id` FROM `manufacturers` WHERE `manufacturer_name` = '$manufacturer' ;"; 
      if(!($manufacturerId = $dblink->query($sqlGet)->fetch_column())) { //db miss
         if(!isset($joeyWordCache[$manufacturer])) { // joey word cache miss
            $possibleJoey = $manufacturer;
            $isJoey = checkSimilarity($errorLogFile, array_keys($manufacturerCache), $manufacturer, $entryNumber);   //check joey entries
            $sqlInsert = "INSERT IGNORE INTO `manufacturers` (`manufacturer_name`) values ('$manufacturer')";
            $sqlGet = "SELECT `manufacturer_id` FROM `manufacturers` WHERE `manufacturer_name` = '$manufacturer' ;"; // refesh with new device type
            //if cant insert attempt to get manufacturer again
            if($dblink->query($sqlInsert) && $dblink->insert_id) {
               $manufacturerId = $dblink->insert_id;
            } else { 
               $manufacturerId = $dblink->query($sqlGet)->fetch_column();
            }

            if($isJoey) {
               $joeyWordCache[$possibleJoey] = $manufacturerId;
            }
         } else {
            $manufacturerId = $joeyWordCache[$manufacturer];
         }
      }
      $manufacturerCache[$manufacturer] = $manufacturerId;
   } else {
      $manufacturerId = $manufacturerCache[$manufacturer]; 
   }
}

function writeDeviceEntry($dblink, $errorLogName, $deviceTypeId, $manufacturerId, $prefix, $body, $entryNumber) {
//attempt to write full device entry into the database
   $sql = "INSERT IGNORE INTO `devices` (`device_type_id`, `manufacturer_id`, `serial_number_prefix`, `serial_number_body` , `line_number`)
     values ('$deviceTypeId', '$manufacturerId', '$prefix', '$body', '$entryNumber')";
   if($dblink->query($sql) && !$dblink->insert_id){
      writeToLog($errorLogName, "DATA ERROR", "entry " . $entryNumber . " is a duplicate");
   }
}

function checkSimilarity($errorLogFile, array $currentEntries, &$newEntry, $entryNumber): bool{
   $candidates = [];
   foreach ($currentEntries as $entry) {
      similar_text($entry, $newEntry, $similarity);

      if (strlen($entry) > strlen($newEntry)) {
         $longerEntry = $entry;
         $shorterEntry = $newEntry;
      }else {
         $longerEntry = $newEntry;
         $shorterEntry = $entry;
      }

      if($similarity >= 65.0 && $longerEntry === $entry){
         $candidates[$similarity] = $entry;
      } else if(str_contains($longerEntry, $shorterEntry)) {
         $candidates[$similarity] = $longerEntry;
      }
   }
   if($candidates) {
         $newString = $candidates[max(array_keys($candidates))];
         writeToLog($errorLogFile, "DATA ERROR (REMIDIATED)", "joey word found at entry " . $entryNumber . "; new string: " . $newString . "; old string: " . $newEntry);
         $newEntry = $newString;
         return true;
      }
   return false;
}
?>
