<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xao="http://xao-php.sourceforge.net/schema/xao_1-0.xsd">
    
    <xsl:output method="html" />

    <xsl:template match="//xao:exceptions">
        <h1>
            Exceptional condition<xsl:if test="count(xao:exception) &gt; 1">s</xsl:if>
        </h1>
        <xsl:apply-templates select="xao:exception" />
    </xsl:template>

    <xsl:template match="xao:exception">
        <div style="font-weight: bold;">
            <xsl:value-of 
                disable-output-escaping="yes"
                select="xao:msg"/>
        </div>
        <xsl:apply-templates select="xao:stack" />
    </xsl:template>
    

    <xsl:template match="xao:stack">
        <div>call stack</div>
        <ul>
            <xsl:apply-templates select="xao:call" />
        </ul>
    </xsl:template>


    <xsl:template match="xao:call">
        <li>
            <xsl:if test="xao:call/@class = ancestor::xao:exception/@class and xao:call/@function = ancestor::xao:exception/@function">
                <xsl:attribute name="style">
                    background: yellow;
                    font-weight: bold;
                </xsl:attribute>
            </xsl:if>
            <div>
                <xsl:value-of select="@file"/>
                (<xsl:value-of select="@line"/>)
                ---&gt;
                <span style="color: red;">
                    <xsl:value-of select="@class"/>
                    <xsl:value-of select="@type"/>
                    <xsl:value-of select="@function"/>
                </span>
                (
                    <xsl:apply-templates 
                        select="xao:args/xao:item[text() != '']" 
                    />
                )
            </div>
        </li>
        <xsl:apply-templates select="xao:call" />
    </xsl:template>
    
    <xsl:template match="xao:args/xao:item">
        <xsl:value-of select="text()"/>,
    </xsl:template>

</xsl:stylesheet>    