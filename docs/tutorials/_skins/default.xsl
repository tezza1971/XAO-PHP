<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet 
    version    ="1.0" 
    xmlns:xsl  ="http://www.w3.org/1999/XSL/Transform"
    xmlns:xao  ="http://xao-php.sourceforge.net/schema/xao_1-0.xsd"
    xmlns:tutor="http://xao-php.sourceforge.net/schema/tutor"
>
    <xsl:import href="../../../classes/XAO_errors.xsl" />
    <xsl:output method="html" />

    <xsl:param name="section" select="''" />
    <xsl:param name="article" select="''" />
    <xsl:param name="articleShort" select="''" />
    <xsl:param name="base" select="''" />

    <xsl:template match="/">
        <html>
            <head>
            	<title>
                    xao-php:tutorials:<xsl:value-of select="$article"/>
                </title>
                <link rel="STYLESHEET" 
                    type="text/css" 
                    href="{$base}tutorials.css" />
                <script language="JavaScript" 
                    src="{$base}tutorials.js" 
                    type="text/javascript">
                </script>
            </head>
            
            <body>
                <div class="pageHeader">XML Application Objects - tutorials</div>

                <xsl:apply-templates select="//xao:exceptions" />
                
                <xsl:choose>
                    <xsl:when test="$article = 'toc'">
                        <h1>Tutorials index</h1>
                        <xsl:apply-templates select="/xaoTutes/toc" mode="navigation"/>
                        <xsl:apply-templates select="/xaoTutes/toc" />
                    </xsl:when>
                    <xsl:otherwise>
                        <h1><xsl:value-of select="$article"/><blink>_</blink></h1>
                        <xsl:apply-templates select="/xaoTutes/toc" mode="navigation" />
                        <xsl:apply-templates select="/xaoTutes/toc/section[@name = $section]/article[@name = $articleShort]/p" mode="overview" />
                        <xsl:apply-templates select="/xaoTutes/article/node()|@*" mode="copy"/>
                    </xsl:otherwise>
                </xsl:choose>
                <br clear="all" />
                <address class="pageFooter">
                	<a href="http://xao-php.sourceforge.net">official homepage</a> | 
                	<a href="http://sourceforge.net/project/showfiles.php?group_id=88235">get latest</a> | 
                	<a href="tutor.php">tutorials TOC</a> | 
                	<a href="../api/index.html">API reference</a>
                	<br />Copyright Terence Kearns 2003
                </address>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="/xaoTutes/toc/section/article/p" mode="overview">
        <xsl:copy-of select="parent::node()" />
    </xsl:template>

    <xsl:template match="/xaoTutes/article//node() | @*" mode="copy">
        <xsl:copy>
            <xsl:apply-templates 
                select="node()[namespace-uri()!='http://xao-php.sourceforge.net/schema/xao_1-0.xsd'] | @*" 
                mode="copy"
            />
        </xsl:copy>
    </xsl:template>
     
    <xsl:template match="/xaoTutes/menu">
        <div class="topMenu">
            <xsl:copy-of select="node()" />
        </div>
    </xsl:template>

    <xsl:template match="/xaoTutes/toc">
        <xsl:copy-of select="p" />
        <xsl:apply-templates select="section" />
    </xsl:template>
    
    <xsl:template match="/xaoTutes/toc/section">
        <h2>
            <xsl:value-of select="@name" />
        </h2>
        <xsl:apply-templates select="article" />
    </xsl:template>

    <xsl:template match="/xaoTutes/toc/section/article">
        <h3>
            <a href="?article={../@name}/{@name}">
                <xsl:value-of select="@name" />
            </a>
        </h3>
        <xsl:copy-of select="p" />
    </xsl:template>

    <xsl:template match="/xaoTutes/toc" mode="navigation">
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
            <tr valign="top">
                <td>
                    <!-- <form action="tutor.php" name="skins" id="skins" style="font-size: 8pt;">
                        select stylesheet (skin)<br />
                        <select name="skin" style="font-size: 8pt;">
                            
                        </select>
                    </form> -->
                </td>
                <td>
                    <div class="topMenu">
                        <strong><a href="tutor.php">Tutorials index</a> |
                    	<a href="../api/index.html">API reference</a> |
                        <a href="http://xao-php.sourceforge.net" target="_blank">XAO homepage</a></strong><br />
                        <xsl:value-of select="@name" />
                        <xsl:apply-templates select="section" mode="navigation" />
                    </div>
                </td>
            </tr>
        </table>
    </xsl:template>

    <xsl:template match="/xaoTutes/toc/section" mode="navigation">
        <xsl:value-of select="@name" />
        <xsl:apply-templates select="article" mode="navigation" /><br />
    </xsl:template>

    <xsl:template match="/xaoTutes/toc/section/article" mode="navigation">
        &gt; <a href="?article={../@name}/{@name}"><xsl:value-of select="@name" /></a>
    </xsl:template>

    <xsl:template match="/xaoTutes/article/highlightFile" mode="copy">
        <div class="floatbox" 
            onclick="ToggleDisplay('{@id}');"
            onmouseover="this.style.cursor = 'hand';"
            onmouseout="this.style.cursor = 'default';"
            title="click to toggle viewing the code."
        >
            <!-- <span class="boxClickNotice">
                click to view
            </span> -->
            <span class="boxCaption">
                <xsl:value-of select="@type" />:
                <xsl:value-of select="@src" />
            </span>
            <div class="snippet" style="display: none;" id="{@id}">
                <xsl:value-of select="node()" disable-output-escaping="yes" />
            </div>
        </div>    
    </xsl:template>

    <xsl:template match="/xaoTutes/article/highlightFile[@type = 'LIVE-OUTPUT']" mode="copy">
        <div class="floatbox">
            <span class="boxCaption" style="text-decoration: none; color: gray;">
                <xsl:value-of select="@type" />:
                <xsl:value-of select="@src" />
            </span>
            <iframe src="{@relSrc}"></iframe>
        </div>    
    </xsl:template>

</xsl:stylesheet>