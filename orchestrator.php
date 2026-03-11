<?php
function orchestrate($dataFile, $processCount, $destDirectory, $un, $pw, $host, $db) {
   shell_exec("./safe-split.sh " . $processCount . " " . $dataFile . " " . $destDirectory);

   $file_names = scandir($destDirectory);
   $lineOffset = 0;
   foreach ($file_names as $file) {

      #skip dot directories
      if ($file == "." || $file == "..") {
         continue;
      }

      $filePath = $destDirectory . '/' . $file;
      
      shell_exec("php run.php " . $un . " " . $pw . " " . $host . " " . $db . " " . $filePath . " " . $lineOffset . " > /dev/null 2>&1 &");
      $lineOffset += intval(shell_exec("wc -l < " . $filePath));
   }
}
?>
