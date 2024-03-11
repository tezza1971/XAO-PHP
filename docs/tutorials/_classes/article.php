<?php
// hmmmmmmmm.... good'ol PHP's "try many paths" feature...
// include_once "../../classes/XAO_DomDoc.php";

class article extends DomDoc {

    var $uriArticle;
    
    var $intSfId = 1;

    function article($uriFile) {
        $this->DomDoc($uriFile,XAO_DOC_READFILE);
        $this->ndRoot->set_attribute(
            "xmlns:tutor",
            "http://xao-php.sourceforge.net/schema/tutor"
        );
        $this->uriArticle = $uriFile;
        $this->SetCustomTagName("highlightFile","GetHighlights");
                                        // here's where all the real work is 
                                        // done for this class.
        $this->ProcessCustomTags();
    }
    
    function GetHighlights($nd) {
        if($nd->has_attribute("src") && $nd->has_attribute("type")) {
            $strSrc = $nd->get_attribute("src");
            $strType = $nd->get_attribute("type");
            $uriFile = dirname($this->uriArticle)."/".$strSrc;
            $nd->set_attribute("relSrc",$uriFile);
            if(file_exists($uriFile) && strlen($strSrc)) {
                if($strType != "LIVE-OUTPUT") {
                    $nd->set_attribute("id","sfid".$this->intSfId++);
                    $cd = $this->objDoc->create_cdata_section(highlight_file($uriFile,true));
                    $nd->append_child($cd);
                }
            }
        }
    }
}

?>