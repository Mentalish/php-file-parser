<?php

function returnFileBuffer(string $filename, int $maxbuffersize): ?string {
   if(!file_exists($filename)){
      return null;
   }

   fopen($filename, "");
}
