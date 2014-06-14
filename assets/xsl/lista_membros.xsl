<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:sp="http://www.w3.org/2005/sparql-results#"
                xmlns="http://www.w3.org/1999/xhtml">

    <xsl:output method="html" indent="no"/>

    <xsl:template match="sp:sparql">
        <html>
            <body>
                <ul id="member_tree" class="member_tree">
                    <xsl:apply-templates/>
                </ul>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="sp:result">

        <li id="{generate-id()}">            
            <span class="member" id="{generate-id()}">
                <xsl:value-of select="normalize-space(sp:binding[@name='localName'])"/>
            </span>            
        </li>     	

    </xsl:template>

</xsl:stylesheet>

