<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:sp="http://www.w3.org/2005/sparql-results#"
                xmlns="http://www.w3.org/1999/xhtml">

    <xsl:output method="html" indent="no"/>

    <xsl:template match="sp:sparql">
        <html>
            <body>
                <ul id="class_tree" class="class_tree">
                    <xsl:apply-templates/>
                </ul>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="sp:result">
        
        <xsl:variable name="type">
            <xsl:value-of select="normalize-space(sp:binding[@name='visivel'])"/>  
        </xsl:variable>
        
        <xsl:variable name="value">
            <xsl:value-of select="normalize-space(sp:binding[@name='localName'])"/>
        </xsl:variable>
            
        <xsl:choose>
            <xsl:when test="$type = 'FALSE'">
                
            </xsl:when>
            <xsl:otherwise>
                <li class="root" id="{generate-id()}">                                       
                    <span class="classMae" id="{generate-id()}" onclick="selectedElement(this)">
                        <b>
                            <xsl:value-of select="normalize-space(sp:binding[@name='localName'])"/>
                        </b>
                    </span>                               
                </li>
            </xsl:otherwise>
        </xsl:choose>

        

    </xsl:template>

</xsl:stylesheet>


