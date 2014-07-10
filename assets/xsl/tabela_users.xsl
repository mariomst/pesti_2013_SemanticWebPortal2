<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:sp="http://www.w3.org/2005/sparql-results#"
                xmlns="http://www.w3.org/1999/xhtml">

    <xsl:output method="html" indent="no"/>

    <xsl:template match="sp:sparql">
        <table border="1">
            <tr>
                <th>Utilizadores</th>
                <th>Opções</th>
            </tr>
            <xsl:apply-templates/>
        </table>
    </xsl:template>

    <xsl:template match="sp:result">
        <tr>
            <td>                
                <xsl:value-of select="normalize-space(sp:binding[@name='Utilizador'])"/>
            </td>            
            <td> 
                <button>                    
                    <img src="/assets/images/delete.png" width="24px" height="24px"/>
                </button>
            </td>
        </tr>
    </xsl:template>

</xsl:stylesheet>