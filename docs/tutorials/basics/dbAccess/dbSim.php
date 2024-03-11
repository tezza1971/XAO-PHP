<?php

class dbSim {

    var $intMinRecords = 10;
    var $intMaxRecords = 30;
    var $intId = 1;
    var $intNow;
    
    var $arrResult = array();
    
    function dbSim() {
        $this->intNow = time();
        $intRecords = mt_rand(
            $this->intMinRecords,
            $this->intMaxRecords
        );
        for($i=0; $i < $intRecords; $i++) {
            $this->arrResult[] = $this->arrGetRecord();
        }
    }
    
    function &arrGetResults() {
        return $this->arrResult;
    }
    
    function arrGetRecord() {
        $arrRecord = array();
        $arrRecord["id"] = $this->intId++;
        $arrRecord["fname"] = $this->strGetRandWord();
        $arrRecord["lname"] = $this->strGetRandWord();
        $arrRecord["dob"] = 
            date("Y-m-d",mt_rand(99999999,$this->intNow));
        return $arrRecord;
    }

    function strGetRandWord($blnTrailSpace = false) {
        $intLen = mt_rand(4,10);
        $strWord = "";
        for($i=0; $i < $intLen; $i++)
            $strWord .= chr(mt_rand(97,122));
        if($blnTrailSpace) $strWord .= " ";
        return $strWord;
    }
}

?>