<?php
include_once "../../classes/XAO_AppDoc.php";
include_once "article.php";

class reader extends AppDoc {
    
    var $arrReq = array();
    
    var $strDocRoot = "xaoTutes";

    var $strSection = "";
    var $strArticleShort = "";
    var $strArticle = "toc";
    var $uriArticle;
    var $ndArticle;
    
    var $strSkin = "default";
    var $uriSkin;
    
    var $ndParams;

    function reader($arrReq) {
                                        // initialise class and set options
        parent::AppDoc($this->strDocRoot);
        $this->blnDebug = true;
        $this->strXsltProcessor = "SABLOTRON";
        $this->ndRoot->set_attribute(
            "xmlns:tutor",
            "http://github.com/tezza1971/XAO-PHP/schema/tutor"
        );
        
        $this->arrReq = $arrReq;
                                        // set up the stylsheet/skin.
        if(isset($this->arrReq["skin"])) 
            $this->strSkin = $this->arrReq["skin"];
        $this->_SetSkin();
        $this->ndSetStylePi($this->uriSkin);
                                        // import the article
        if(isset($this->arrReq["article"])) {
            $this->strArticle = $this->arrReq["article"];
            $this->uriArticle = $this->strArticle.".xml";
            if(file_exists($this->uriArticle)) {
                $this->ndArticle =& 
                    $this->ndConsumeDoc(new article($this->uriArticle));
            }
        }
                                        // set the section if applicable
        $arrPath = explode("/",$this->strArticle);
        if(count($arrPath) > 1) {
            $this->strSection = $arrPath[0];
            $this->strArticleShort = $arrPath[1];
        }
                                        // import the table of contents
        $this->ndConsumeFile("toc.xml");
                                        // send some params to stylesheet
        $this->arrXslParams = array(
            "article" => $this->strArticle,
            "base" => "_skins/".$this->strSkin."/",
            "section" => $this->strSection,
            "articleShort" => $this->strArticleShort
        );
    }
    
    function _SetSkin() {
        $this->uriSkin = "_skins/".$this->strSkin.".xsl";
        if(!file_exists($this->uriSkin)) {
            $this->strSkin = "default";
            $this->uriSkin = "_skins/".$this->strSkin.".xsl";
        }
    }
}

?>