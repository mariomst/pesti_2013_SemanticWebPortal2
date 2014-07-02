<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:sp="http://www.w3.org/2005/sparql-results#"
                xmlns="http://www.w3.org/1999/xhtml">

    <xsl:output method="html" indent="no"/>

    <xsl:template match="sp:sparql">
        <html>
            <body>
                <ul id="subclass_tree" class="subclass_tree">
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
                <li id="{generate-id()}">
                    <button>
                        <xsl:attribute name="onclick">                           
                            <xsl:text>elementVisibility('</xsl:text>
                            <xsl:value-of select="$value" />
                            <xsl:text>', 2);</xsl:text>
                        </xsl:attribute>
                        <img src="/assets/images/eye_closed.png" width="24px" height="24px"/>
                    </button>                    
                </li>
            </xsl:when>
            <xsl:otherwise>                
                <li id="{generate-id()}">
                    <button>
                        <xsl:attribute name="onclick">                           
                            <xsl:text>elementVisibility('</xsl:text>
                            <xsl:value-of select="$value" />
                            <xsl:text>', 1);</xsl:text>
                        </xsl:attribute>
                        <img src="/assets/images/eye_open.png" width="24px" height="24px"/>
                    </button>
                    <span class="subclass" id="{generate-id()}" onclick="selectedElement(this)">
                        <xsl:value-of select="normalize-space(sp:binding[@name='localName'])"/>
                    </span>            
                </li>     
            </xsl:otherwise>
        </xsl:choose>	

    </xsl:template>

</xsl:stylesheet>

