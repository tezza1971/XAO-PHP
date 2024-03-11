<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    
    <!-- The XSLT processor built into the DOMXML extension doesn't
    support IMPORT statements, at least not very well anyway. So we must
    use SABLOTRON -->
    
    <xsl:import href="XAO_errors.xsl" />
    
    <xsl:output method="html" />

    <xsl:template match="/">
        <html>
            <head>
            	<title>
                    <xsl:value-of select="blogApp/appTitle"/>
                </title>
            </head>
            
            <body>
                <h1>
                    <xsl:value-of 
                        select="blogApp/appTitle"/>
                </h1>
                <p>
                    <xsl:value-of 
                        select="blogApp/appInfo"/>
                </p>
                <ul>
                    <xsl:apply-templates select="//log" />
                </ul>
                <!-- <xsl:apply-templates 
                    select="//xao:exceptions" /> -->
            </body>
        </html>
    </xsl:template>

    <xsl:template match="log">
        <li>
            <xsl:value-of select="@ODBCformat"/> -
            <xsl:value-of select="text()"/>
        </li>
    </xsl:template>
</xsl:stylesheet>