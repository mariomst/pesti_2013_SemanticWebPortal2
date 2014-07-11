<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:sp="http://www.w3.org/2005/sparql-results#"
                xmlns="http://www.w3.org/1999/xhtml">

    <xsl:output method="html" indent="no"/>

    <xsl:template match="sp:sparql">
        <table border="1">
            <tr>
                <th width="90%">Utilizadores</th>
                <th>Opções</th>
            </tr>
            <xsl:apply-templates/>
        </table>
    </xsl:template>

    <xsl:template match="sp:result">
        <xsl:variable name="user">
            <xsl:value-of select="normalize-space(sp:binding[@name='Utilizador'])"/>
        </xsl:variable>       
        
        <xsl:choose>
            <xsl:when test="$user = 'Admin'">                
            </xsl:when>
            <xsl:otherwise>
                <tr>
                    <td align="center">                
                        <xsl:value-of select="normalize-space(sp:binding[@name='Utilizador'])"/>
                    </td>            
                    <td align="center"> 
                        <button>     
                            <xsl:attribute name="onclick">
                                <xsl:text>deleteUser('</xsl:text>
                                <xsl:value-of select="normalize-space(sp:binding[@name='Utilizador'])"/>
                                <xsl:text>');</xsl:text>
                            </xsl:attribute>               
                            <img src="/assets/images/delete.png" width="24px" height="24px"/>
                        </button>
                    </td>
                </tr>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>