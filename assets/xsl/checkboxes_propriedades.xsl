<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:sp="http://www.w3.org/2005/sparql-results#"
                xmlns="http://www.w3.org/1999/xhtml">

    <xsl:output method="html" indent="no"/>

    <xsl:template match="sp:sparql">
        <html>
            <body>         
                <xsl:apply-templates/>            
            </body>
        </html>
    </xsl:template>

    <xsl:template match="sp:result">
        <xsl:variable name="type" select="generate-id()"/>

        <tr>
            <xsl:attribute name="id">
                <xsl:value-of select="$type"/>
            </xsl:attribute>
            <td>
                <button>
                    <xsl:attribute name="onclick">
                        <xsl:text>botaoAdd('</xsl:text>
                        <xsl:value-of select="$type"/>
                        <xsl:text>');return false;</xsl:text>
                    </xsl:attribute>
                    <img src="/assets/images/add.png" width="24px" height="24px"/>
                </button>
            </td>
            <td id="valor">
                <xsl:attribute name="value">
                    <xsl:value-of select="normalize-space(sp:binding[@name='Propriedades'])"/>
                </xsl:attribute>
                <xsl:value-of select="normalize-space(sp:binding[@name='Propriedades'])"/>
            </td>
        </tr>
    </xsl:template>

</xsl:stylesheet>