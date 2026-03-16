<?php
include_once 'log.php';
include_once 'parser_helpers.php';

function parseTokens(array $tokens, int $numParameters, int &$lineCount, string $errorLogName, $dblink, &$deviceTypeCache, &$manufacturerCache): void {
   $TYPO_MANUFACTURER = "/(^[a-z])|([0-9!@#$%^&*+=()'`_\-?<>;:|\[\]\\\])/";
   $TYPO_DEVICE_TYPE = "/[0-9!@#$%^&*+=()'`_\-?<>;:|\[\]\\\]/";
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
      if ($errorLine == false) {
         writeDeviceType($errorLogName, $dblink, $deviceTypeCache, $deviceType, $deviceTypeId);
         writeManufacturer($errorLogName, $dblink, $manufacturerCache, $manufacturer, $manufacturerId);
         writeDeviceEntry($dblink, $errorLogName, $deviceTypeId, $manufacturerId, $prefix, $body, $lineCount); 
      }
   }
}
?>
