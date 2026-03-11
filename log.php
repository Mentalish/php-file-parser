<?php
function writeToLog($logfilePath, $logType, $message, $lineCount) : void {
   $logfile = fopen($logfilePath, 'a');
   fwrite($logfile, "[ " . date(DATE_RFC2822) . "] - [" . $logType . "] - " . $message . "\n");
   fclose($logfile);
}
?>
