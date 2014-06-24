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
                    <th>Tipo</th>
                    <th>Caracteristicas</th>
                    <th>Propriedade2</th>
                    <th>Range</th>
                </tr>
                <xsl:apply-templates/>
            </table>
        </body>
    </html>
</xsl:template>

<xsl:template match="sp:result">    
    <tr>
        <td>
            <xsl:value-of select="normalize-space(sp:binding[@name='Tipo'])"/>
        </td>
        <td>
            <xsl:value-of select="normalize-space(sp:binding[@name='Caracteristicas'])"/>
        </td>
        <td>
            <xsl:value-of select="normalize-space(sp:binding[@name='Propriedade2'])"/>
        </td>
        <td>
            <xsl:value-of select="normalize-space(sp:binding[@name='Range'])"/>
        </td>                 
    </tr>
</xsl:template>

</xsl:stylesheet>