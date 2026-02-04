<?php

/**
 * return an array of parameters of the file given the file name and the max memory that can be used
 * @return ?string[][]
 */
function returnFileBuffer(string $filename, int $maxbuffersize): ?array {
   if(!file_exists($filename)){
      return null;
   }

   $file = fopen($filename, "r");

   $buffer = fread($file, $maxbuffersize);
   $index = strlen($buffer) - 1;

   //partial entry in buffer
   while ($buffer[$index] != '\n' && !feof($file)) {
      $index--;
   }

   $buffer = substr($buffer, 0, $index);

   $entries = explode('\n', $buffer);

   foreach ($entries as $entry) {
      $entry = explode(',', $entry);
   }

   return $entries;
}
