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
                        <th>Propriedade</th>
                        <th>Valor</th>
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
                        <xsl:value-of select="normalize-space(sp:binding[@name='Propriedade'])"/>
                    </xsl:attribute>
                    <xsl:attribute name="onclick">
                        <xsl:text>callFunctionsFromLink('</xsl:text>
                        <xsl:value-of select="normalize-space(sp:binding[@name='Propriedade'])"/>
                        <xsl:text>',5);return false;</xsl:text>
                    </xsl:attribute>
                    <xsl:value-of select="normalize-space(sp:binding[@name='Propriedade'])"/>
                </a>
            </td>
            <td>
                <xsl:value-of select="normalize-space(sp:binding[@name='AlgunsValoresDe'])"/>
            </td>
        
            <td>
                <button>
                    <xsl:attribute name="onclick">
                        <xsl:text>callFunctionsFromLink('</xsl:text>
                        <xsl:value-of select="normalize-space(sp:binding[@name='Propriedade'])"/>
                        <xsl:text>',5);</xsl:text>
                    </xsl:attribute>
                    <img src="/assets/images/magnifying_glass.png" width="24px" height="24px"/>
                </button>
                <button>
                    <xsl:attribute name="onclick">
                        <xsl:text>callFunctionsforProperties('classe','</xsl:text>
                        <xsl:value-of select="normalize-space(sp:binding[@name='Propriedade'])"/>
                        <xsl:text>','</xsl:text>
                        <xsl:value-of select="normalize-space(sp:binding[@name='AlgunsValoresDe'])"/>
                        <xsl:text>');</xsl:text>
                    </xsl:attribute>
                    <img src="/assets/images/delete.png" width="24px" height="24px"/>
                </button>
            </td>
           
        </tr>
    </xsl:template>

</xsl:stylesheet>