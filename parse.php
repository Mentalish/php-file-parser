<?php
include_once 'log.php';
include_once 'parser_helpers.php';

function parseTokens(array $tokens, int $numParameters, int &$lineCount, string $errorLogName, $dblink, &$deviceTypeCache, &$manufacturerCache): void {
   $TYPO_REGEX = "/[0-9!@#$%^&*+=()'`_\?<>;:|\[\]\\\-]/";
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
         if(checkAndFixTypo($TYPO_REGEX, $deviceType, "device type", $lineCount, $errorLogName)) {
         $errorLine = true;
         }
      }

      if(detectEmpty($manufacturer, "manufacturer", $lineCount, $errorLogName)) {
         $errorLine = true;
      } else {
         if(checkAndFixTypo($TYPO_REGEX, $manufacturer, "manufacturer", $lineCount, $errorLogName)) {
            $errorLine = true;
         } else if(checkAndFixCase($manufacturer, "manufacturer", $lineCount, $errorLogName)) {
            
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
      if ($errorLine == false) {
         writeDeviceType($errorLogName, $dblink, $deviceTypeCache, $deviceType, $deviceTypeId, $lineCount);
         writeManufacturer($errorLogName, $dblink, $manufacturerCache, $manufacturer, $manufacturerId, $lineCount);
         writeDeviceEntry($dblink, $errorLogName, $deviceTypeId, $manufacturerId, $prefix, $body, $lineCount); 
      }
   }
}
?>
