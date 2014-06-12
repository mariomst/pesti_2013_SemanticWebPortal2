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

    <xsl:variable name="type">
        <xsl:value-of select="normalize-space(sp:binding[@name='Tipo'])"/>  <!-- A variável type fica com o tipo de Propriedade (ObjectProperty ou DatatypeProperty) -->
    </xsl:variable>

    <tr>
        <xsl:choose>
            <xsl:when test="$type = 'ObjectProperty'">
                <td>
                    <a> <!-- Vai mostrar a URI e a acção é a chamada do consultMember -->
                        <xsl:attribute name="href">
                            <xsl:value-of select="normalize-space(sp:binding[@name='valor'])"/>
                        </xsl:attribute>
                        <xsl:attribute name="onclick">
                            <xsl:text>callFunctionsFromLink('</xsl:text>
                                <xsl:value-of select="normalize-space(sp:binding[@name='Valor'])"/>
                            <xsl:text>',2);return false;</xsl:text>
                        </xsl:attribute>
                        <xsl:value-of select="normalize-space(sp:binding[@name='valor'])"/>
                    </a>
                </td>
                 <td>
                    <a> <!-- Vai mostrar a propriedade e o endereço vai ser o URI presente no XML e a acção é a chamada do consultMember -->
                        <xsl:attribute name="href">
                            <xsl:value-of select="normalize-space(sp:binding[@name='valor'])"/>
                        </xsl:attribute>
                        <xsl:attribute name="onclick">
                            <xsl:text>callFunctionsFromLink('</xsl:text>
                                <xsl:value-of select="normalize-space(sp:binding[@name='Valor'])"/>
                            <xsl:text>',2);return false;</xsl:text>
                        </xsl:attribute>
                        <xsl:value-of select="normalize-space(sp:binding[@name='Propriedade'])"/>
                    </a>
                </td>
                <td>
                    <a> <!-- Vai mostrar o valor da propriedade e o endereço vai ser o URI presente no XML e a acção é a chamada do consultMember -->
                        <xsl:attribute name="href">
                            <xsl:value-of select="normalize-space(sp:binding[@name='valor'])"/>
                        </xsl:attribute>
                        <xsl:attribute name="onclick">
                            <xsl:text>callFunctionsFromLink('</xsl:text>
                                <xsl:value-of select="normalize-space(sp:binding[@name='Valor'])"/>
                            <xsl:text>',2);return false;</xsl:text>
                        </xsl:attribute>
                        <xsl:value-of select="normalize-space(sp:binding[@name='Valor'])"/>
                    </a>
                </td>
                <td>
                    <button>
                        <xsl:attribute name="onclick">
                            <xsl:text>callFunctionsFromLink('</xsl:text>
                                <xsl:value-of select="normalize-space(sp:binding[@name='Valor'])"/>
                            <xsl:text>',2);</xsl:text>
                        </xsl:attribute>
                        <img src="/assets/images/magnifying_glass.png" width="24px" height="24px"/>
                    </button>
                    <button>
                        <xsl:attribute name="onclick">testes()</xsl:attribute>
                            <img src="/assets/images/delete.png" width="24px" height="24px"/>
                    </button>
                </td>
            </xsl:when>
            <xsl:otherwise> <!-- Quando é DatatypeProperty -->
                <td>
                    
                </td>
                <td>
                    <xsl:value-of select="normalize-space(sp:binding[@name='Propriedade'])"/>
                </td>
                <td>
                    <xsl:value-of select="normalize-space(sp:binding[@name='Valor'])"/>
                </td>
                <td>
                    <button>
                        <xsl:attribute name="onclick">testes()</xsl:attribute>
                        <img src="/assets/images/delete.png" width="24px" height="24px"/>
                    </button>
                </td>
            </xsl:otherwise>
        </xsl:choose>
    </tr>
</xsl:template>

</xsl:stylesheet>