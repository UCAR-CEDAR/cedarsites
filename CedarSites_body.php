<?php
class CedarSites extends SpecialPage
{
    var $dbuser, $dbpwd ;

    function CedarSites() {
	SpecialPage::SpecialPage("CedarSites");
	#wfLoadExtensionMessages( 'CedarSites' ) ;

	$this->dbuser = "madrigal" ;
	$this->dbpwd = "shrot-kash-iv-po" ;
    }
    
    function execute( $par ) {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer ;
	
	$this->setHeaders();

	CedarNote::addScripts() ;

	$wgOut->addScript( "<script language=\"javascript\">

	function check_cedar_value(text_id,value_text,min_value,max_value) {
	    var elem=document.getElementById(text_id);
	    if (elem)
	    {
		if (max_value != -1 && min_value != -1)
		{
		    if (elem.value < min_value || elem.value > max_value)
		    {
			alert_value=value_text+\" must be between \" + min_value + \" and \" + max_value;
			alert(alert_value);
		    }
		}
		else if (max_value == -1)
		{
		    if (elem.value < min_value)
		    {
			alert_value=value_text+\" must be greater than \" + min_value;
			alert(alert_value);
		    }
		}
		else if (min_value == -1)
		{
		    if (elem.value > max_value)
		    {
			alert_value=value_text+\" must be less than \" + max_value;
			alert(alert_value);
		    }
		}
	    }
	}
	</script>\n" ) ;

	$sort_param = $wgRequest->getText('sort');
	$sort_by = "ID" ;
	$action = $wgRequest->getText('action');
	$site = $wgRequest->getInt('site');
	if( $action == "detail" )
	{
	    $this->siteDetail( $site ) ;
	    return ;
	}
	else if( $action == "create" )
	{
	    $this->siteEdit( $site, 1, $action ) ;
	    return ;
	}
	else if( $action == "edit" )
	{
	    $this->siteEdit( $site, 0, $action ) ;
	    return ;
	}
	else if( $action == "delete" )
	{
	    $this->siteDelete( $site ) ;
	    return ;
	}
	else if( $action == "update" )
	{
	    $this->siteUpdate( $site ) ;
	    return ;
	}
	else if( $action == "newnote" )
	{
	    $is_successful = CedarNote::newNote( "tbl_site", "ID", $site ) ;
	    if( $is_successful )
	    {
		$this->siteDetail( $site ) ;
	    }
	    return ;
	}
	else if( $action == "delete_note" )
	{
	    $is_successful = CedarNote::deleteNote( "CedarSites", "site", $site, "tbl_site", "ID" ) ;
	    if( $is_successful )
	    {
		$this->siteDetail( $site ) ;
	    }
	    return ;
	}
	else if( $action == "edit_note" )
	{
	    $is_successful = CedarNote::editNoteForm( "CedarSites", "site", $site ) ;
	    if( !$is_successful )
	    {
		$this->siteDetail( $site ) ;
	    }
	    return ;
	}
	else if( $action == "update_note" )
	{
	    $is_successful = CedarNote::updateNote( ) ;
	    if( $is_successful )
	    {
		$this->siteDetail( $site ) ;
	    }
	    return ;
	}
	else if( $action == "sort" )
	{
	    if( $sort_param == "long" )
	    {
		$sort_by = "LONG_NAME" ;
	    }
	    else if( $sort_param == "short" )
	    {
		$sort_by = "SHORT_NAME" ;
	    }
	}
	$this->displaySites( $sort_by ) ;
    }

    private function displaySites( $sort_by )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;

	if( $allowed )
	{
	    $wgOut->addHTML( "    <TABLE ALIGN=\"CENTER\" BORDER=\"0\" WIDTH=\"100%\" CELLPADDING=\"0\" CELLSPACING=\"0\">\n" ) ;
	    $wgOut->addHTML( "	<TR>\n" ) ;
	    $wgOut->addHTML( "	    <TD WIDTH=\"100%\" ALIGN=\"LEFT\">\n" ) ;
	    $wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarSites?action=create'>Create a New Site</A></SPAN>\n" ) ;
	    $wgOut->addHTML( "	    </TD>\n" ) ;
	    $wgOut->addHTML( "	</TR>\n" ) ;
	    $wgOut->addHTML( "    </TABLE>\n" ) ;
	    $wgOut->addHTML( "    <BR/>\n" ) ;
	}

	// Get the catalog database
	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database\n" ) ;
	    return ;
	}
	else
	{
	    // sort_by variable is created within this script and not
	    // passed in by a client, no need to clean it
	    $res = $dbh->query( "select ID, KINST, SHORT_NAME, LONG_NAME from tbl_site ORDER BY $sort_by" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }
	    else
	    {
		$wgOut->addHTML( "    <TABLE ALIGN=\"CENTER\" BORDER=\"1\" WIDTH=\"100%\" CELLPADDING=\"0\" CELLSPACING=\"0\">\n" ) ;
		$wgOut->addHTML( "	<TR style=\"background-color:gainsboro;\">\n" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"10%\" ALIGN=\"CENTER\">\n" ) ;
		$wgOut->addHTML( "	        &nbsp;\n" ) ;
		$wgOut->addHTML( "	    </TD>\n" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"10%\" ALIGN=\"CENTER\">\n" ) ;
		$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarSites?action=sort&sort=id'>ID</A></SPAN>\n" ) ;
		$wgOut->addHTML( "	    </TD>\n" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"10%\" ALIGN=\"CENTER\">\n" ) ;
		$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarSites?action=sort&sort=kinst'>KINST</A></SPAN>\n" ) ;
		$wgOut->addHTML( "	    </TD>\n" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"20%\" ALIGN=\"CENTER\">\n" ) ;
		$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarSites?action=sort&sort=short'>Short Name</A></SPAN>\n" ) ;
		$wgOut->addHTML( "	    </TD>\n" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"50%\" ALIGN=\"CENTER\">\n" ) ;
		$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarSites?action=sort&sort=long'>Long Name</A></SPAN>\n" ) ;
		$wgOut->addHTML( "	    </TD>\n" ) ;
		$wgOut->addHTML( "	</TR>\n" ) ;
		$rowcolor="white" ;
		while( ( $obj = $dbh->fetchObject( $res ) ) )
		{
		    $site = intval( $obj->ID ) ;
		    $kinst = intval( $obj->KINST ) ;
		    $short_name = $obj->SHORT_NAME ;
		    $long_name = $obj->LONG_NAME ;
		    // skip the empty site
		    if( $site == 0 )
			continue ;
		    $wgOut->addHTML( "	<TR style=\"background-color:$rowcolor;\">\n" ) ;
		    if( $rowcolor == "white" ) $rowcolor = "gainsboro" ;
		    else $rowcolor = "white" ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"10%\" ALIGN=\"CENTER\">\n" ) ;
		    $wgOut->addHTML( "		<A HREF='$wgServer/wiki/index.php/Special:CedarSites?action=detail&site=$site'><IMG SRC='$wgServer/wiki/icons/detail.png' ALT='detail' TITLE='Detail'></A>" ) ;
		    if( $allowed )
		    {
			$wgOut->addHTML( "&nbsp;&nbsp;<A HREF='$wgServer/wiki/index.php/Special:CedarSites?action=edit&site=$site'><IMG SRC='$wgServer/wiki/icons/edit.png' ALT='edit' TITLE='Edit'></A>&nbsp;&nbsp;<A HREF='$wgServer/wiki/index.php/Special:CedarSites?action=delete&site=$site'><IMG SRC='$wgServer/wiki/icons/delete.png' ALT='delete' TITLE='Delete'></A>\n" ) ;
		    }
		    $wgOut->addHTML( "	    </TD>\n" ) ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"10%\" ALIGN=\"CENTER\">\n" ) ;
		    $wgOut->addHTML( "		<SPAN STYLE=\"font-size:9pt;\">$site</SPAN>\n" ) ;
		    $wgOut->addHTML( "	    </TD>\n" ) ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"10%\" ALIGN=\"CENTER\">\n" ) ;
		    $wgOut->addHTML( "		<SPAN STYLE=\"font-size:9pt;\">$kinst</SPAN>\n" ) ;
		    $wgOut->addHTML( "	    </TD>\n" ) ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"20%\" ALIGN=\"CENTER\">\n" ) ;
		    $wgOut->addHTML( "		<SPAN STYLE=\"font-size:9pt;\">$short_name</SPAN>\n" ) ;
		    $wgOut->addHTML( "	    </TD>\n" ) ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"50%\" ALIGN=\"LEFT\">\n" ) ;
		    $wgOut->addHTML( "		<SPAN STYLE=\"font-size:9pt;\">&nbsp;&nbsp;&nbsp;$long_name</SPAN>\n" ) ;
		    $wgOut->addHTML( "	    </TD>\n" ) ;
		    $wgOut->addHTML( "	</TR>\n" ) ;
		}
		$wgOut->addHTML( "</TABLE>\n" ) ;
	    }
	    $dbh->close() ;
	}
    }

    private function siteDetail( $site )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;

	$wgOut->addHTML( "<SPAN STYLE=\"font-size:12pt;\">Return to the <A HREF=\"$wgServer/wiki/index.php/Special:CedarSites\">Observation Site List</A></SPAN><BR /><BR />\n" ) ;

	// Get the catalog database
	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database\n" ) ;
	    return ;
	}

	$site = $dbh->strencode( $site ) ;

	$res = $dbh->query( "select i.KINST, i.PREFIX, i.INST_NAME, s.SHORT_NAME, s.LONG_NAME, s.DESCRIPTION, s.LAT_DEGREES, s.LAT_MINUTES, s.LAT_SECONDS, s.LON_DEGREES, s.LON_MINUTES, s.LON_SECONDS, s.ALT, s.NOTE_ID from tbl_site s, tbl_instrument i WHERE ID = $site AND s.KINST = i.KINST" ) ;
	if( !$res )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}
	else
	{
	    $obj = $dbh->fetchObject( $res ) ;
	    if( $obj )
	    {
		$kinst = intval( $obj->KINST ) ;
		$prefix = $obj->PREFIX ;
		$inst_name = $obj->INST_NAME ;
		$short_name = $obj->SHORT_NAME ;
		$long_name = $obj->LONG_NAME ;
		$description = $obj->DESCRIPTION ;
		$lat_degrees = $obj->LAT_DEGREES ;
		$lat_minutes = $obj->LAT_MINUTES ;
		$lat_seconds = $obj->LAT_SECONDS ;
		$lon_degrees = $obj->LON_DEGREES ;
		$lon_minutes = $obj->LON_MINUTES ;
		$lon_seconds = $obj->LON_SECONDS ;
		$alt = $obj->ALT ;
		$note_id = intval( $obj->NOTE_ID ) ;

		$wgOut->addHTML( "    <TABLE ALIGN=\"LEFT\" BORDER=\"1\" WIDTH=\"800\" CELLPADDING=\"0\" CELLSPACING=\"0\">\n" ) ;
		$wgOut->addHTML( "        <TR>\n" ) ;
		$wgOut->addHTML( "            <TD ALIGN='CENTER' HEIGHT='30px' BGCOLOR='Aqua'>\n" ) ;
		if( $allowed )
		{
		    $wgOut->addHTML( "                <A HREF='$wgServer/wiki/index.php/Special:CedarSites?action=edit&site=$site'><IMG SRC='$wgServer/wiki/icons/edit.png' ALT='edit' TITLE='Edit'></A>&nbsp;&nbsp;<A HREF='$wgServer/wiki/index.php/Special:CedarSites?action=delete&site=$site'><IMG SRC='$wgServer/wiki/icons/delete.png' ALT='delete' TITLE='Delete'></A>&nbsp;&nbsp;\n" ) ;
		}
		$wgOut->addHTML( "                <SPAN STYLE='font-weight:bold;font-size:14pt;'>$short_name - $long_name</SPAN>\n" ) ;
		$wgOut->addHTML( "            </TD>\n" ) ;
		$wgOut->addHTML( "        </TR>\n" ) ;
		$wgOut->addHTML( "        <TR>\n" ) ;
		$wgOut->addHTML( "            <TD BGCOLOR='White'>\n" ) ;
		$wgOut->addHTML( "                <DIV STYLE='line-height:2.0;font-weight:normal;font-size:10pt;'>\n" ) ;
		$wgOut->addHTML( "                    Instrument: <A HREF=\"$wgServer/wiki/index.php/Special:Cedar_Instruments?action=detail&kinst=$kinst\">$kinst - $prefix - $inst_name</A><BR />\n" ) ;
		$wgOut->addHTML( "                    Latitude: $lat_degrees&#176; $lat_minutes&#39; $lat_seconds&#34;<BR />\n" ) ;
		$wgOut->addHTML( "                    Longitude: $lon_degrees&#176; $lon_minutes&#39; $lon_seconds&#34;<BR />\n" ) ;
		$wgOut->addHTML( "                    Altitude: $alt<BR />\n" ) ;
		$wgOut->addHTML( "                    Description:<BR /><SPAN STYLE=\"line-height:1.0;\">$description</SPAN><BR /><BR />\n" ) ;
		$wgOut->addHTML( "                </DIV>\n" ) ;
		$wgOut->addHTML( "            </TD>\n" ) ;
		$wgOut->addHTML( "        </TR>\n" ) ;
		$wgOut->addHTML( "        <TR>\n" ) ;
		$wgOut->addHTML( "            <TD BGCOLOR='White'>\n" ) ;
		$wgOut->addHTML( "                <DIV STYLE='font-weight:normal;font-size:10pt;'>\n" ) ;
		$wgOut->addHTML( "                    Notes:<BR />\n" ) ;
		$last_note_id = CedarNote::displayNote( $note_id, "CedarSites", "site", $site, 0, $dbh ) ;
		CedarNote::newNoteForm( $last_note_id, "CedarSites", "site", $site ) ;
		$wgOut->addHTML( "                </DIV>\n" ) ;
		$wgOut->addHTML( "            </TD>\n" ) ;
		$wgOut->addHTML( "        </TR>\n" ) ;
		$wgOut->addHTML( "    </TABLE>\n" ) ;
	    }
	    else
	    {
		$wgOut->addHTML( "There is no Observational Site with the given id: $site<BR />\n" ) ;
	    }
	}

	$dbh->close() ;
    }

    private function siteEdit( $site, $isnew, $action )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;
	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to edit instrument information</SPAN><BR />\n" ) ;
	    return ;
	}

	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$site = $dbh->strencode( $site ) ;
	$short_name = "" ;
	$long_name = "" ;
	$kinst = 0 ;
	$prefix = "" ;
	$inst_name = "" ;
	$description = "" ;
	$duty_cycle = "" ;
	$operational_hours = "" ;
	$lat_degrees = 0 ;
	$lat_minutes = 0 ;
	$lat_seconds = "" ;
	$lon_degrees = 0 ;
	$lon_minutes = 0 ;
	$lon_seconds = "" ;
	$alt = "" ;
	$ref_url = "" ;
	if( $isnew == 0 && $action == "edit" )
	{
	    $res = $dbh->query( "select i.KINST, s.SHORT_NAME, s.LONG_NAME, s.DESCRIPTION, s.LAT_DEGREES, s.LAT_MINUTES, s.LAT_SECONDS, s.LON_DEGREES, s.LON_MINUTES, s.LON_SECONDS, s.ALT from tbl_site s, tbl_instrument i WHERE s.ID = $site AND i.KINST = s.KINST" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    if( $res->numRows() != 1 )
	    {
		$dbh->close() ;
		$wgOut->addHTML( "Unable to edit the site $site, does not exist<BR />\n" ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    $obj = $dbh->fetchObject( $res ) ;
	    if( !$obj )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    $kinst = intval( $obj->KINST ) ;
	    $short_name = $obj->SHORT_NAME ;
	    $long_name = $obj->LONG_NAME ;
	    $description = $obj->DESCRIPTION ;
	    $lat_degrees = intval( $obj->LAT_DEGREES ) ;
	    $lat_minutes = intval( $obj->LAT_MINUTES ) ;
	    $lat_seconds = $obj->LAT_SECONDS ;
	    $lon_degrees = intval( $obj->LON_DEGREES ) ;
	    $lon_minutes = intval( $obj->LON_MINUTES ) ;
	    $lon_seconds = $obj->LON_SECONDS ;
	    $alt = $obj->ALT ;
	}
	else if( $action == "update" )
	{
	    $kinst = $wgRequest->getInt( 'kinst' ) ;
	    $short_name = $dbh->strencode( $wgRequest->getText( 'short_name' ) ) ;
	    $long_name = $dbh->strencode( $wgRequest->getText( 'long_name' ) ) ;
	    $description = $dbh->strencode( $wgRequest->getText( 'description' ) ) ;
	    $lat_degrees = $wgRequest->getInt( 'lat_degrees' ) ;
	    $lat_minutes = $wgRequest->getInt( 'lat_minutes' ) ;
	    $lat_seconds = $dbh->strencode( $wgRequest->getText( 'lat_seconds' ) ) ;
	    $lon_degrees = $wgRequest->getInt( 'lon_degrees' ) ;
	    $lon_minutes = $wgRequest->getInt( 'lon_minutes' ) ;
	    $lon_seconds = $dbh->strencode( $wgRequest->getText( 'lon_seconds' ) ) ;
	    $alt = $dbh->strencode( $wgRequest->getText( 'alt' ) ) ;
	}

	// now display the information in the form
	$wgOut->addHTML( "<FORM name=\"site_edit\" action=\"$wgServer/wiki/index.php/Special:CedarSites\" method=\"POST\">\n" ) ;
	$wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"action\" value=\"update\">\n" ) ;
	$wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"site\" value=\"$site\">\n" ) ;
	$wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"isnew\" value=\"$isnew\">\n" ) ;
	$wgOut->addHTML( "  <TABLE WIDTH=\"800\" CELLPADDING=\"2\" CELLSPACING=\"0\" BORDER=\"0\">\n" ) ;

	// site short_name text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Short Name:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" name=\"short_name\" size=\"30\" value=\"$short_name\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// site long_name text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Long Name:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" name=\"long_name\" size=\"50\" value=\"$long_name\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// instrument selection, must select something
	$res = $dbh->query( "select KINST, PREFIX, INST_NAME from tbl_instrument" ) ;
	if( !$res )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Instrument:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <select name=\"kinst\">\n" ) ;
	while( ( $obj = $dbh->fetchObject( $res ) ) )
	{
	    $new_kinst = intval( $obj->KINST ) ;
	    $prefix = $obj->PREFIX ;
	    $inst_name = $obj->INST_NAME ;
	    if( $new_kinst == $kinst )
	    {
		$wgOut->addHTML( "          <option value=\"$new_kinst\" selected>$new_kinst - $prefix - $inst_name</option>\n" ) ;
	    }
	    else
	    {
		$wgOut->addHTML( "          <option value=\"$new_kinst\">$new_kinst - $prefix - $inst_name</option>\n" ) ;
	    }
	}
	$wgOut->addHTML( "        </select>\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// site latitude text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Latitude:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" id=\"lat_degrees\" name=\"lat_degrees\" size=\"5\" value=\"$lat_degrees\" onchange=\"check_cedar_value('lat_degrees','Latitude Degrees',-180,179)\">&#176&nbsp;" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" id=\"lat_minutes\" name=\"lat_minutes\" size=\"5\" value=\"$lat_minutes\" onchange=\"check_cedar_value('lat_minutes','Latitude Minutes',0,59)\">&#39&nbsp;" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" id=\"lat_seconds\" name=\"lat_seconds\" size=\"5\" value=\"$lat_seconds\" onchange=\"check_cedar_value('lat_seconds','Latitude Seconds',0,59.99)\">&#34<BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// site longitude text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Longitude:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" id=\"lon_degrees\" name=\"lon_degrees\" size=\"5\" value=\"$lon_degrees\" onchange=\"check_cedar_value('lon_degrees','Longitude Degrees',-180,180)\">&#176&nbsp;" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" id=\"lon_minutes\" name=\"lon_minutes\" size=\"5\" value=\"$lon_minutes\" onchange=\"check_cedar_value('lon_minutes','Longitude Minutes',0,59)\">&#39&nbsp;" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" id=\"lon_seconds\" name=\"lon_seconds\" size=\"5\" value=\"$lon_seconds\" onchange=\"check_cedar_value('lon_seconds','Longitude Seconds',0,59.99)\">&#34<BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// site altitude text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Altitude:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" id=\"alt\" name=\"alt\" size=\"5\" value=\"$alt\" onchange=\"check_cedar_value('alt','Altitude',0,-1)\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// description text area
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Description:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <TEXTAREA STYLE=\"width:75%;border-color:black;border-style:solid;border-width:thin;\" ID=\"description\" NAME=\"description\" rows=\"10\" cols=\"20\">$description</TEXTAREA><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// submit, cancel and reset buttons
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        &nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Submit\">\n" ) ;
	$wgOut->addHTML( "        &nbsp;&nbsp;<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Cancel\">\n" ) ;
	$wgOut->addHTML( "        &nbsp;&nbsp;<INPUT TYPE=\"RESET\" VALUE=\"Reset\">\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;
	$wgOut->addHTML( "  </TABLE>\n" ) ;

	$wgOut->addHTML( "</FORM>\n" ) ;

	$dbh->close() ;
    }

    private function siteUpdate( $site )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;
	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to edit site information</SPAN><BR />\n" ) ;
	    return ;
	}

	// if the cancel button was pressed then go to site detail
	$submit = $wgRequest->getText( 'submit' ) ;
	if( $submit == "Cancel" )
	{
	    if( $site == 0 )
		$this->displaySites( "ID" ) ;
	    else
		$this->siteDetail( $site ) ;
	    return ;
	}

	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$site = $dbh->strencode( $site ) ;
	$isnew = $wgRequest->getInt( 'isnew' ) ;
	$kinst = $wgRequest->getInt( 'kinst' ) ;
	$short_name = $dbh->strencode( $wgRequest->getText( 'short_name' ) ) ;
	$long_name = $dbh->strencode( $wgRequest->getText( 'long_name' ) ) ;
	$description = $dbh->strencode( $wgRequest->getText( 'description' ) ) ;
	$lat_degrees = $wgRequest->getInt( 'lat_degrees' ) ;
	$lat_minutes = $wgRequest->getInt( 'lat_minutes' ) ;
	$lat_seconds = $dbh->strencode( $wgRequest->getText( 'lat_seconds' ) ) ;
	$lon_degrees = $wgRequest->getInt( 'lon_degrees' ) ;
	$lon_minutes = $wgRequest->getInt( 'lon_minutes' ) ;
	$lon_seconds = $dbh->strencode( $wgRequest->getText( 'lon_seconds' ) ) ;
	$alt = $dbh->strencode( $wgRequest->getText( 'alt' ) ) ;

	$found_error = 0 ;
	if( $lat_degrees < -180 || $lat_degrees > 179 )
	{
	    $found_error++ ;
	    $wgOut->addHTML( "<SPAN STYLE='font-weight:bold;color:red;'>Latitude Degrees should be between -180 and 179, trying to set to $lat_degrees</SPAN><BR />\n" ) ;
	}
	if( $lat_minutes < 0 || $lat_minutes > 59 )
	{
	    $found_error++ ;
	    $wgOut->addHTML( "<SPAN STYLE='font-weight:bold;color:red;'>Latitude Minutes should be between 0 and 59, trying to set to $lat_minutes</SPAN><BR />\n" ) ;
	}
	if( $lat_seconds < 0 || $lat_seconds > 59.99 )
	{
	    $found_error++ ;
	    $wgOut->addHTML( "<SPAN STYLE='font-weight:bold;color:red;'>Latitude Seconds should be between 0 and 59, trying to set to $lat_seconds</SPAN><BR />\n" ) ;
	}
	if( $lon_degrees < -180 || $lon_degrees > 179 )
	{
	    $found_error++ ;
	    $wgOut->addHTML( "<SPAN STYLE='font-weight:bold;color:red;'>Longitude Degrees should be between -180 and 179, trying to set to $lon_degrees</SPAN><BR />\n" ) ;
	}
	if( $lon_minutes < 0 || $lon_minutes > 59 )
	{
	    $found_error++ ;
	    $wgOut->addHTML( "<SPAN STYLE='font-weight:bold;color:red;'>Longitude Minutes should be between 0 and 59, trying to set to $lon_minutes</SPAN><BR />\n" ) ;
	}
	if( $lon_seconds < 0 || $lon_seconds > 59.99 )
	{
	    $found_error++ ;
	    $wgOut->addHTML( "<SPAN STYLE='font-weight:bold;color:red;'>Longitude Seconds should be between 0 and 59, trying to set to $lon_seconds</SPAN><BR />\n" ) ;
	}
	if( $alt < 0.0 )
	{
	    $found_error++ ;
	    $wgOut->addHTML( "<SPAN STYLE='font-weight:bold;color:red;'>Altitude should be a positive number, trying to set to $alt</SPAN><BR />\n" ) ;
	}
	if( $found_error > 0 )
	{
	    $wgOut->addHTML( "<SPAN STYLE='font-weight:bold;color:red;'>Errors found in your changes, please correct and re-submit</SPAN><BR /><BR />\n" ) ;
	    $this->siteEdit( $site, $isnew, "update" ) ;
	    return ;
	}

	// if isnew then insert the new instrument
	// if not new, kinst != 0, then update kinst (remember to use new_kinst)
	if( $isnew == 1 )
	{
	    $insert_success = $dbh->insert( 'tbl_site',
		    array(
			    'KINST' => $kinst,
			    'SHORT_NAME' => $short_name,
			    'LONG_NAME' => $long_name,
			    'DESCRIPTION' => $description,
			    'LAT_DEGREES' => $lat_degrees,
			    'LAT_MINUTES' => $lat_minutes,
			    'LAT_SECONDS' => $lat_seconds,
			    'LON_DEGREES' => $lon_degrees,
			    'LON_MINUTES' => $lon_minutes,
			    'LON_SECONDS' => $lon_seconds,
			    'ALT' => $alt,
		    ),
		    __METHOD__
	    ) ;

	    if( $insert_success == false )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Failed to insert new site $short_name<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    $site = $dbh->insertId() ;
	}
	else if( $isnew == 0 )
	{
	    $update_success = $dbh->update( 'tbl_site',
		    array(
			    'KINST' => $kinst,
			    'SHORT_NAME' => $short_name,
			    'LONG_NAME' => $long_name,
			    'DESCRIPTION' => $description,
			    'LAT_DEGREES' => $lat_degrees,
			    'LAT_MINUTES' => $lat_minutes,
			    'LAT_SECONDS' => $lat_seconds,
			    'LON_DEGREES' => $lon_degrees,
			    'LON_MINUTES' => $lon_minutes,
			    'LON_SECONDS' => $lon_seconds,
			    'ALT' => $alt,
		    ),
		    array(
			    'ID' => $site
		    ),
		    __METHOD__
	    ) ;

	    if( $update_success == false )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Failed to update kinst $kinst<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }
	}

	$dbh->close() ;

	$this->siteDetail( $site ) ;
    }

    private function siteDelete( $site )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer, $wgUser ;

	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;
	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to delete sites</SPAN><BR />\n" ) ;
	    return ;
	}

	// grab the catalog database
	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	// first make sure the site exists
	$site = $dbh->strencode( $site ) ;

	$res = $dbh->query( "select ID from tbl_site WHERE ID = $site" ) ;
	if( !$res )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	if( $res->numRows() != 1 )
	{
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to delete the Observational Site, site $site does not exist<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	// ask for confirmation
	$confirm = $wgRequest->getText( 'confirm' ) ;

	if( !$confirm )
	{
	    $dbh->close() ;
	    $wgOut->addHTML( "Are you sure you want to delete the site with id$site?\n" ) ;
	    $wgOut->addHTML( "(<A HREF=\"$wgServer/wiki/index.php/Special:CedarSites?action=delete&confirm=yes&site=$site\">Yes</A>" ) ;
	    $wgOut->addHTML( " | <A HREF=\"$wgServer/wiki/index.php/Special:CedarSites?action=delete&confirm=no&site=$site\">No</A>)" ) ;
	    return ;
	}

	if( $confirm && $confirm == "no" )
	{
	    $dbh->close() ;
	    $this->siteDetail( $site ) ;
	    return ;
	}

	// delete the site and all of its notes
	if( $confirm && $confirm == "yes" )
	{
	    // need to delete all of the associated notes as well
	    $res = $dbh->query( "select NOTE_ID from tbl_site WHERE ID = $site" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    $obj = $dbh->fetchObject( $res ) ;
	    if( $obj )
	    {
		$note_id = intval( $obj->NOTE_ID ) ;
		if( $note_id != 0 )
		{
		    CedarNote::deleteNotes( $note_id, $dbh ) ;
		}
	    }

	    // delete the site
	    $delete_success = $dbh->delete( 'tbl_site', array( 'ID' => $site ) ) ;

	    if( $delete_success == false )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Failed to delete site $site:<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }
	}

	$dbh->close() ;

	$this->displaySites( "ID" ) ;

	return ;
    }
}
?>
