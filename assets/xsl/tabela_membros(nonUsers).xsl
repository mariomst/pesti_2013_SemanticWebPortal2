<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:sp="http://www.w3.org/2005/sparql-results#"
                xmlns="http://www.w3.org/1999/xhtml">

    <xsl:output method="html" indent="no"/>

    <xsl:template match="sp:sparql">
        <html>
            <head>
                <title>
                    SPARQL XSLT
                </title>
            </head>
            <body>
                <table border="1">
                    <tr>
                        <th>URI</th>
                        <th>Label</th>
                        <th>Opções</th>
                    </tr>
                    <xsl:apply-templates/>
                </table>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="sp:result">
        <tr>
            <td>
                <a>
                    <xsl:attribute name="href">
                        <xsl:value-of select="normalize-space(sp:binding[@name='membro'])"/>
                    </xsl:attribute>
                    <xsl:attribute name="onclick">
                        <xsl:text>callFunctionsFromLink('</xsl:text>
                        <xsl:value-of select="normalize-space(sp:binding[@name='localName'])"/>
                        <xsl:text>',2);return false;</xsl:text>
                    </xsl:attribute>
                    <xsl:value-of select="normalize-space(sp:binding[@name='membro'])"/>
                </a>
            </td>
            <td>
                <a>
                    <xsl:attribute name="href">
                        <xsl:value-of select="normalize-space(sp:binding[@name='membro'])"/>
                    </xsl:attribute>
                    <xsl:attribute name="onclick">
                        <xsl:text>callFunctionsFromLink('</xsl:text>
                        <xsl:value-of select="normalize-space(sp:binding[@name='localName'])"/>
                        <xsl:text>',2);return false;</xsl:text>
                    </xsl:attribute>
                    <xsl:value-of select="normalize-space(sp:binding[@name='localName'])"/>
                </a>
            </td>
            <td>
                <button>
                    <xsl:attribute name="onclick">
                        <xsl:text>callFunctionsFromLink('</xsl:text>
                        <xsl:value-of select="normalize-space(sp:binding[@name='localName'])"/>
                        <xsl:text>',2);</xsl:text>
                    </xsl:attribute>
                    <img src="/assets/images/magnifying_glass.png" width="24px" height="24px"/>
                </button>                
            </td>
        </tr>
    </xsl:template>

</xsl:stylesheet>