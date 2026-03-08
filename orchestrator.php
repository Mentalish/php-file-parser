<?php
function orchestrate($dataFile, $processCount, $destDirectory, $un, $pw, $host, $db) {
   shell_exec("./safe-split.sh " . $processCount . " " . $dataFile . " " . $destDirectory);

   $file_names = scandir($destDirectory);

   foreach ($file_names as $file) {

      #skip dot directories
      if ($file == "." || $file == "..") {
         continue;
      }

      $filePath = $destDirectory . '/' . $file;
      shell_exec("./run.php " . $un . " " . $pw . " " . $host . " " . $db . " " . $filePath . " 2>&1 &");
   }
}
?>
