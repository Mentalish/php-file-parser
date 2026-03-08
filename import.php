<?php
include 'orchestrator.php';
$operators = "";
$operators .= "u:p:h:n:f:c:d:";  // Required value
$operators .= "w::"; // Optional value

$configuration = getopt($operators);

$un = $configuration["u"];
$pw = $configuration["p"];
$host =$configuration["h"];
$db = $configuration["n"];
$dataFile = $configuration["f"];
$processCount = $configuration["c"];
$dataDirectory = $configuration["d"];

orchestrate($dataFile, $processCount, $dataDirectory, $un, $pw, $host, $db);
?>
