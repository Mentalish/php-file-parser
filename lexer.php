<?php
function getEntries($file, $entriesPerBuffer): ?array{
   $tokens = [];

   for ($i=0; $i < $entriesPerBuffer ; $i++) {
      $entry = fgetcsv($file, 0, ",");

      if($entry == false) {
         break;
      }

      $tokens[] = $entry;
   }
   if ($tokens) {
      return $tokens;
   } else {
      return null;
   }
}
?>
