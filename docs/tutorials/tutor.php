<?php
include_once "_classes/reader.php";

$objAppDoc = new reader($_REQUEST);
$objAppDoc->Transform();
$objAppDoc->Send();

?>