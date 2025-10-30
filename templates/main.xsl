<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" encoding="UTF-8" indent="yes"/>

    <xsl:param name="CURRENT_YEAR"/>

    <xsl:template match="/">
        <html>
            <head>
                <title><xsl:value-of select="/root/title"/></title>
                <link rel="stylesheet" href="https://unpkg.com/sakura.css/css/sakura.css" type="text/css" />
            </head>
            <body>
                <h1><xsl:value-of select="/root/title"/></h1>
                <p><xsl:value-of select="/root/message"/></p>
                <footer>
                    <p>Copyright <xsl:value-of select="$CURRENT_YEAR"/> XAO-PHP</p>
                </footer>
            </body>
        </html>
    </xsl:template>

</xsl:stylesheet>
