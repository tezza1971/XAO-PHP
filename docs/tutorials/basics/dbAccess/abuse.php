<?php
include_once "../../../../classes/XAO_AppDoc.php"; // master document XAO class 
include_once "../../../../classes/XAO_DbToXml.php"; // database to XML converter

$strDocRoot = "tutorialDbAccess";       // set variable to pass by reference
$objAppDoc = new AppDoc($strDocRoot);   // instantiate master document

    $arrAbuse = array($_GET,$_POST,$_COOKIE); // array of assc arrays :)
    $objDbRes = new DbToXml($arrAbuse); // get DB content obj
    $objDbRes->SetResultTagName("abuse");
    $objDbRes->SetRowTagName("array");
    $objDbRes->Execute();               // perform the conversion

$objAppDoc->ndConsumeDoc($objDbRes);    // aggregate the content obj
$objAppDoc->Send();                     // transmit raw XML to browser
?>