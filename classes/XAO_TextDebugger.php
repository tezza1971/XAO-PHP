<?php

/**
* Plain Text debugger
*
* This class provides developer-friendly HTML output of strings for debugging.
* Specifically, it highlights the specified line of the text and can display
* the surrounding text using an nominal number of lines for padding. This
* functionality can be used anywhere a parse failure accurs and there is a line
* number involved.
*
* @author       Terence Kearns
* @version      0.2
* @copyright    Terence Kearns 2003
* @license      Apache License, Version 2.0 (see http://www.apache.org/licenses/LICENSE-2.0 )
* @package      XAO
* @link         https://github.com/tezza1971/XAO-PHP
*/
class TextDebugger {

    public string $strHtml;
    
    public array $arrText;
    
    public int $intLine;
    
    public int $intPadding = 10;
    
    public string $strHighlightStyle = 
        "color: red; font-weight: bold; background: yellow;";
    
    public string $strTextStyle = 
        "color: black; font-weight: normal; background: #E0E0E0;";

    public string $strBorderStyle = 
        "border: solid red 1px; padding: 10px; font-size: 8pt;";

    public function __construct(string $strText, int $intLine = -1) {
        if($intLine == -1) {
            $this->intPadding = 0;
        }
        if(file_exists($strText)) {
            $this->arrText = file($strText);
        }
        else {
            $this->arrText = explode("\n",$strText);
        }
        $this->intLine = $intLine;
    }
    
    public function strGetHtml(): string {
        
        if(!strlen($this->strHtml)) {
            $intPadding = $this->intPadding;
            $intHilightLine = $this->intLine;
                
            $strOut = "<pre style=\"".$this->strBorderStyle."\">Debug dump: ";
            
            if($intPadding < 0) {
                $intPadding *= -1; // deal with negative numbers
            }
            if($intPadding != 0) {
                $strOut .= 
                    "printing ".$intPadding
                    ." lines either side of highlight.\n";
            }
            
            $intCurrLine = 1;
            foreach($this->arrText as $strLine) {
                if(
                    (
                        $intHilightLine > -1 
                        &&  ( 
                               ($intHilightLine - $intCurrLine) <= $intPadding
                            && ($intCurrLine - $intHilightLine) <= $intPadding
                        )
                    )
                    || $intPadding == 0
                ) {
                    if($intCurrLine == $intHilightLine) {
                        $strOut .= 
                            "<div style=\"".$this->strHighlightStyle."\">";
                    }
                    else {
                        $strOut .= "<div style=\"".$this->strTextStyle."\">";
                    }
                    $strOut .= 
                        sprintf(
                            "<b>%5d</b> %s",
                            $intCurrLine,
                            htmlentities($strLine)
                        );
                    $strOut .= "</div>";
                    if($intCurrLine == count($this->arrText)) {
                        $strOut .= "End of File!\n";
                    }
                }
                $intCurrLine++;
            }
            $strOut .= "</pre>\n";
            $this->strHtml = $strOut;
        }
            
        return $this->strHtml;
    }
}

