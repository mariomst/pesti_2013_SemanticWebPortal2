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
                        <th>Range</th>
                        <th>Caracteristicas</th>
                        <th>Propriedade</th>
                    </tr>
                    <xsl:apply-templates/>       
                </table>       
            </body>
        </html>
    </xsl:template>

    <xsl:template match="sp:result">            
        <xsl:variable name="char">
            <xsl:value-of select="normalize-space(sp:binding[@name='Caracteristicas'])"/>
        </xsl:variable>
        
        <xsl:variable name="prop2">
            <xsl:value-of select="normalize-space(sp:binding[@name='Propriedade2'])"/>
        </xsl:variable>
        
        <xsl:variable name="range">
            <xsl:value-of select="normalize-space(sp:binding[@name='Range'])"/>
        </xsl:variable>
        
        
        <tr>        
            <td>
                <xsl:value-of select="normalize-space(sp:binding[@name='Tipo'])"/>
            </td>
        
            <xsl:choose>
                <xsl:when test="$range !=''">    
                    <td>
                        <xsl:value-of select="normalize-space(sp:binding[@name='Range'])"/>
                    </td>            
                </xsl:when>
                <xsl:otherwise>
                    <td>  </td>
                </xsl:otherwise>
            </xsl:choose>                
        
            <xsl:choose>
                <xsl:when test="$char !=''">                    
                    <td>
                        <xsl:value-of select="normalize-space(sp:binding[@name='Caracteristicas'])"/>
                    </td>                                                                                       
                </xsl:when>
                <xsl:otherwise>                    
                    <td>  </td>
                </xsl:otherwise>
            </xsl:choose>   

                    
            <xsl:choose>
                <xsl:when test="$prop2 !=''">                    
                    <td>
                        <xsl:value-of select="normalize-space(sp:binding[@name='Propriedade2'])"/>
                    </td>                                                                                       
                </xsl:when>
                <xsl:otherwise>                    
                    <td>  </td>
                </xsl:otherwise>
            </xsl:choose>    
            
        </tr>                       
    </xsl:template>

</xsl:stylesheet>