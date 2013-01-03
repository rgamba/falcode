<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="html" encoding="UTF-8" indent="no"/>
<xsl:include href="../common/PHPReport.xsl"/>

<xsl:param name="sql"/>
<xsl:param name="user"/>            
<xsl:param name="pass"/>
<xsl:param name="conn"/>
<xsl:param name="interface"/>
<xsl:param name="database"/>
<xsl:param name="nodatamsg"/>

<xsl:template match="/">
    <xsl:apply-templates/>
</xsl:template>

</xsl:stylesheet>
