<?php
include_once "dbSim.php";               // database simulator utility
include_once "../../../../classes/XAO_AppDoc.php"; // master document XAO class 
include_once "../../../../classes/XAO_DbToXml.php"; // database to XML converter

$strDocRoot = "tutorialDbAccess";       // set variable to pass by reference
$objAppDoc = new AppDoc($strDocRoot);   // instantiate master document

    $objDb = new dbSim;                 // get our tutorial database simulator
    $objDbRes = new DbToXml($objDb->arrGetResults()); // get DB content obj
    $objDbRes->Execute();               // perform the conversion

$objAppDoc->ndConsumeDoc($objDbRes);    // aggregate the content obj
$objAppDoc->Send();                     // transmit raw XML to browser
?>