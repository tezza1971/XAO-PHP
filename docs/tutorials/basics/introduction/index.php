<?php
include_once "../../../../classes/XAO_AppDoc.php"; // input XAO class 
$strDocRoot = "tutorial1";              // set variable to pass by reference
$objAppDoc = new AppDoc($strDocRoot);   // instantiate master document
$objAppDoc->blnDebug = true;            // turn on debugging
$objAppDoc->ndConsumeFile("data/blog.xml"); // graft XML file into master doc
$objAppDoc->ndSetStylePi("skins/index.xsl"); // nominate stylesheet
$objAppDoc->Transform();                // execute XSLT processor
$objAppDoc->Send();                     // transmit to browser
?>