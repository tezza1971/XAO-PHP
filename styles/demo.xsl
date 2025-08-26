<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:output method="html" indent="yes"/>

  <!-- Root template: dump node tree as HTML -->
  <xsl:template match="/">
    <div class="xao-demo">
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="*">
    <div class="node">
      <span class="tag">&lt;<xsl:value-of select="name()"/>&gt;</span>
      <xsl:if test="@*">
        <ul class="attrs">
          <xsl:for-each select="@*">
            <li><strong>@<xsl:value-of select="name()"/>:</strong> <xsl:value-of select="."/></li>
          </xsl:for-each>
        </ul>
      </xsl:if>
      <div class="children">
        <xsl:apply-templates/>
      </div>
      <span class="tag">&lt;/<xsl:value-of select="name()"/>&gt;</span>
    </div>
  </xsl:template>

  <xsl:template match="text()[normalize-space(.)!='']">
    <span class="text"><xsl:value-of select="normalize-space(.)"/></span>
  </xsl:template>
</xsl:stylesheet>
