<?php
// the line below is only needed if the include path is not set on php.ini
//ini set("include path",ini get("include path").":/usr/lib/phpreports/");
include_once "PHPReportMaker.php";
$oRpt = new PHPReportMaker();
$oRpt->setUser("dbuser");
$oRpt->setPassword("dbpass");
$oRpt->setXML("sales.xml");
$oRpt->run();
?>