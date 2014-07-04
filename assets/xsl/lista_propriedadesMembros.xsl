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
                <ul id="member_properties" class="member_properties">
                    <xsl:apply-templates/>
                </ul>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="sp:result">

        <li id="{generate-id()}">            
            <span class="property" id="{generate-id()}">
                <xsl:value-of select="normalize-space(sp:binding[@name='Propriedade'])"/>
            </span>
            <xsl:text> -> </xsl:text>      
            <span class="value" id="{generate-id()}">
                <xsl:value-of select="normalize-space(sp:binding[@name='Valor'])"/>
            </span>      
        </li>    
            
    </xsl:template>

</xsl:stylesheet>