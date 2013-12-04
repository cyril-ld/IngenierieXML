<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:math="http://exslt.org/math">
    <xsl:output  method="xml" encoding="utf8"  indent="yes"/>
        <xsl:template match="/">
            <liste-equisport>
                <xsl:for-each select="document/data/element">
                    <equipsport>
                        <xsl:attribute name="nom"><xsl:value-of select="geo/name" /></xsl:attribute>
                        <xsl:attribute name="adresse"><xsl:value-of select="ADRESSE" /></xsl:attribute>
                        <xsl:variable name="lat_es" select="number(translate(substring-before(_l, ' , '),'[] ', ''))"/>
                        <xsl:variable name="lat_es_rad" select="number($lat_es *3.14 div 180)"/>
                        <xsl:variable name="lon_es" select="number(translate(substring-after(_l, ' , '),'[]', ''))"/>
                        <xsl:variable name="lon_es_rad" select="number($lon_es *3.14 div 180)"/>
                        
                        <xsl:for-each select="document('LOC_EQUIPUB_MOBILITE_NM_STBL.xml')/document/mobidata/element">
                            <xsl:variable name="lat_em" select="number(translate(substring-before(_l, ' , '),'[] ', ''))"/>
                            <xsl:variable name="lat_em_rad" select="number($lat_em * 3.14 div 180)"/>
                            <xsl:variable name="lon_em" select="number(translate(substring-after(_l, ' , '),'[]', ''))"/>
                            <xsl:variable name="lon_em_rad" select="number($lon_em * 3.14 div 180)"/>
                            
                            <xsl:variable name="x" select="number(($lon_em_rad - $lon_es_rad) * math:cos(($lat_es_rad+$lat_em_rad) div 2))"/>
                            <xsl:variable name="y" select="number($lat_em_rad - $lat_es_rad)"/>
                               
                            <xsl:variable name="dist" select="number(math:sqrt($x*$x + $y*$y) * 6371009)"/>
                            
                            <xsl:if test="$dist &lt; 500">
                                <mobi-proxi>
                                    <nom><xsl:value-of select="geo/name"/></nom>
                                    <categorie><xsl:value-of select="LIBCATEGORIE"/></categorie>
                                    <adresse><xsl:value-of select="ADRESSE"/></adresse>
                                    <distance><xsl:value-of select="floor($dist)"/></distance>
                                </mobi-proxi>
                            </xsl:if>
                        </xsl:for-each>
                    </equipsport>
                </xsl:for-each>
            </liste-equisport>
        </xsl:template>
</xsl:stylesheet>