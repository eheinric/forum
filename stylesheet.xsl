<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template match="/">
  <html>
  <body>

  <h2>Forum '<xsl:value-of select="forum/@title"/>', contributions by <xsl:value-of select="forum/@user"/></h2>
    <table border="1" cellpadding="4">
      <xsl:for-each select="forum/discussion">
        <tr bgcolor="#999999">
          <td>
            <xsl:element name="a">
            <xsl:attribute name="href">
              <xsl:value-of select="@address"/>
            </xsl:attribute>
            <xsl:attribute name="target">
            _blank
            </xsl:attribute>
            <xsl:value-of select="@title"/>
          </xsl:element>
          </td>
        </tr>
        <xsl:for-each select="post">
	  <tr bgcolor="#E0E0E0">
	    <td>
		  <xsl:value-of select="@date"/>, 
		  <xsl:element name="a">
		    <xsl:attribute name="href">
		      <xsl:value-of select="@address"/>
		    </xsl:attribute>
		    <xsl:attribute name="target">
		    _blank
		    </xsl:attribute>
		    <xsl:value-of select="@desc"/>
		  </xsl:element>
	    </td>
	  </tr>
	  <tr>
	    <td>
	      <xsl:for-each select="text">
	        <xsl:value-of select="."/><br/>
	      </xsl:for-each>
	    </td>
	  </tr>
        </xsl:for-each>
      </xsl:for-each>
    </table>
  </body>
  </html>
</xsl:template>
</xsl:stylesheet>