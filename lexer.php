<?php

/**
 * return an array of parameters of the file given the file name and the max memory that can be used
 * @return ?string[][]
 */
function returnFileBuffer($file, int $maxbuffersize): ?array {
   $buffer = fread($file, $maxbuffersize);
   $index = strlen($buffer) - 1;
   $rewind = 0;
   //partial entry in buffer
   while ($index >= 0 && $buffer[$index] != "\n" && !feof($file)) {
      $index--;
      $rewind++;
   }
   if($index >= 0) {
      $buffer = substr($buffer, 0, $index);
      fseek($file, -$rewind, SEEK_CUR);
   }
   

   $entries = explode("\n", $buffer);
   $entriesArr = [];

   foreach ($entries as $entry) {
      $entriesArr[] = explode(',', $entry);
   }

   return $entriesArr;
}
