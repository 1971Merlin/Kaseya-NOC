<?php
//  date_default_timezone_set ( "Australia/Adelaide" );

// read the ini file //
  $config = parse_ini_file("noc.ini", true);
  $serverName = $config['server']['SQLserver'];
  $connectionInfo = $config['connectionInfo'];
  $connectionInfo['LoginTimeout'] = 5;

  
  
// check connection // 

$conn = sqlsrv_connect( $serverName, $connectionInfo);
if( $conn === false )
{
  echo "<body>";
  echo "<p>Unable to connect to SQL Server!<br/>";
  echo "Current configuration:</p>";
  echo "<P class=\"ini\">Server : ".$serverName."<br/>";
  echo "User Name : ".$connectionInfo["UID"]."<br/>";
  echo "Database : ".$connectionInfo["Database"];
  echo "</p>";
  echo "<p>Please check/edit noc.ini using the <A href=\"editsettings.php\">edit settings</a> page.</p>";
  echo "</body>";
  echo "</html>";
  die();
}
  
	
// setup various global variables //

  $NOCtitle = $config['config']['NOCtitle'];


// org and scope //
  $org_filter = $config['config']['org_filter'];
  $scope_filter = $config['config']['scope_filter'];
 
  

  if ($scope_filter!=null) { $scope_filter_sql = " and foo.scope_ref = '".$scope_filter."'"; }
/*the next line may be redundant by the following code ? */
 
  $usescopefilter = false; 
  
/* check if scope is user-defined */
  $tsql = "select ref from adminScope where ref='".$scope_filter."' and internalCode = 0";
  $stmt = sqlsrv_query( $conn, $tsql);
  if( $stmt === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }

  while ( $row=sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) )
  {
	if ($row['ref']==$scope_filter) { $usescopefilter = true; }
  }
 
 
  
/* you only need a scope filter if a user-defined scope is selected
   i.e. it appears in dbo.adminScope
   the system ORGs are filtered out of all scope selectors, so you can't use any form of scope filtering to view a set ORG!!!!
   
   thus, (for example) you can't see the kserver machine if you make any SQL Query that involves vdb_scopes_machines or similar.
   
  also, if you have no user-defined scopes at all, the whole thing bombs entirely!!
  
  so, scope filtering must be optional and engaged only when a user-defined scope has been selected.
  
  */


// org/scope error checking! //

if ($org_filter=="" or $scope_filter=="") {
echo "<body>";
echo "<p>Scope and ORG filters not defined in noc.ini!!<br/>";
echo "<p>Please check/edit noc.ini using the <A href=\"editsettings.php\">edit settings</a> page.</p>";
echo "<script>window.location = 'editsettings.php?alert=true'</script>";
echo "</body>";
echo "</html>";
die();

}



  

// check for missing noc.ini settings
  
if (!isset($config['strip']['showPendApprove'])) { $config['strip']['showPendApprove'] = false; }  
if (!isset($config['strip']['showOldAgents'])) { $config['strip']['showOldAgents'] = true; }  
if (!isset($config['strip']['showOlServers'])) { $config['strip']['showOlServers'] = true; }
if (!isset($config['strip']['showUngrouped'])) { $config['strip']['showUngrouped'] = true; }
if (!isset($config['strip']['showPendReboot'])) { $config['strip']['showPendReboot'] = true; }
if (!isset($config['strip']['showSuspended'])) { $config['strip']['showSuspended'] = true; }
if (!isset($config['strip']['showMobile'])) { $config['strip']['showMobile'] = true; }
if (!isset($config['strip']['showOlGraph'])) { $config['strip']['showOlGraph'] = true; }
if (!isset($config['strip']['showRCHistory'])) { $config['strip']['showRCHistory'] = true; }
if (!isset($config['strip']['showVSAUsers'])) { $config['strip']['showVSAUsers'] = true; }
if (!isset($config['strip']['showSecurity'])) { $config['strip']['showSecurity'] = false; }
if (!isset($config['strip']['showAV'])) { $config['strip']['showAV'] = false; }
if (!isset($config['strip']['showBUDR'])) { $config['strip']['showBUDR'] = false; }
if (!isset($config['strip']['showPolicy'])) { $config['strip']['showPolicy'] = false; }
if (!isset($config['strip']['showEXT'])) { $config['strip']['showEXT'] = true; }
if (!isset($config['strip']['showRSS'])) { $config['strip']['showRSS'] = true; }
if (!isset($config['strip']['showLastCheckin'])) { $config['strip']['showLastCheckin'] = false; }
if (!isset($config['strip']['showScripts'])) { $config['strip']['showScripts'] = true; }
if (!isset($config['strip']['extURL'])) { $config['strip']['extURL'] = null; }
if (!isset($config['strip']['rssURL'])) { $config['strip']['rssURL'] = null; }


if (!isset($config['panels']['showCounts'])) { $config['panels']['showCounts'] = true; }
if (!isset($config['panels']['showPolicy'])) { $config['panels']['showPolicy'] = true; }
if (!isset($config['panels']['showSD'])) { $config['panels']['showSD'] = true; }
if (!isset($config['panels']['showMGS'])) { $config['panels']['showMGS'] = true; }
if (!isset($config['panels']['showBUDR'])) { $config['panels']['showBUDR'] = true; }
if (!isset($config['panels']['showAv'])) { $config['panels']['showAv'] = true; }
if (!isset($config['panels']['showKAV'])) { $config['panels']['showKAV'] = true; }
if (!isset($config['panels']['showSEP'])) { $config['panels']['showSEP'] = true; }
if (!isset($config['panels']['showAlarms'])) { $config['panels']['showAlarms'] = true; }
if (!isset($config['panels']['showUptime'])) { $config['panels']['showUptime'] = true; }
if (!isset($config['panels']['showPatching'])) { $config['panels']['showPatching'] = true; }
if (!isset($config['panels']['showLowDisk'])) { $config['panels']['showLowDisk'] = true; }
if (!isset($config['panels']['showRC'])) { $config['panels']['showRC'] = true; }

// load an external image?
  $exturl = $config['strip']['extURL'];

// RSS?
  $rssurl = $config['strip']['rssURL'];
    
// if no AV module (i.e. KS panel turned off), don't query AV stuff!!   used in pendingreboot panel
  $avon = $config['panels']['showAv'];

// if no BUDR, don't query BUDR stuff!! used in dotstatus panel
  $buon = $config['panels']['showBUDR'];

// if no patch, don't do Patch dialogs!!
  $paton = $config['panels']['showPatching'];


  
// detect server version
$conn = sqlsrv_connect( $serverName, $connectionInfo);
$tsql = "select paramValue as version from kserverParams where paramName = 'version'";

$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false ) {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
$KVer = $row['version'];
$KVer = substr($KVer,0,strrpos($KVer,'.'));
$KVer = substr($KVer,0,strrpos($KVer,'.'));
  
  

//disable module for R9!!  
if ($KVer > 8.0 ) { $config['strip']['showMobile'] = false; }

  
if (!isset($config['config']['alarm'])) { $config['config']['alarm'] = true; }  
// play audible alarm at server down

  $alarmon = $config['config']['alarm'];  
  
  
// format dates & times to preferred format  


if (!isset($config['config']['dfmt1'])) { $config['config']['dfmt1'] = "d"; }
if (!isset($config['config']['dsep'])) { $config['config']['dsep'] = "-"; }
if (!isset($config['config']['dfmt2'])) { $config['config']['dfmt2'] = "m"; }
if (!isset($config['config']['dfmt3'])) { $config['config']['dfmt3'] = "Y"; }
if (!isset($config['config']['tsep'])) { $config['config']['tsep'] = ":"; }
if (!isset($config['config']['tampm'])) { $config['config']['tampm'] = "a"; }

if (!isset($config['config']['resultCount'])) { $config['config']['resultCount'] = 12; }


  
  $datestyle = $config['config']['dfmt1'].$config['config']['dsep'].$config['config']['dfmt2'].$config['config']['dsep'].$config['config']['dfmt3']; 

  $ts = $config['config']['tampm'];
  if ($ts=="")  $timestyle = "H".$config['config']['tsep']."i"; else $timestyle = "h".$config['config']['tsep']."i ".$ts;
 
 
// limit to number of records returned in alarms and backup logs
  $resultcount = $config['config']['resultCount'];

  
// ----------------------------------------------------------------------------------------- //
	
// global functions follow //




function showAgentIcon($online,$login) {
  switch($online) {
    case 0 : echo "<img src=\"images/stop_offline.gif\">";
	         break;
			 
	case 1 : if ($login==null) { echo "<img src=\"images/go.gif\">";} else { echo "<img src=\"images/go_login.gif\">"; }
	         break;

	case 2 : echo "<img src=\"images/go_login_idle.gif\">";
	         break;
			 
	case 198 : echo "<img src=\"images/SymbolStop2.gif\">";
			 break;

	case 199 : echo "<img src=\"images/stop_checkin.gif\">";
			 break;
			 
	default : echo "<img src=\"images/go.gif\">";
	         break;
	}
}

function showDot($dottype) {
	$plural = ($dottype <>1 ? "s" : "" );
  switch ($dottype) {
    case 0 : return "<img src=\"images/status_green.gif\">";
	         break;
			 
    default : return "<img src=\"images/status_red.gif\" title=\"{$dottype} issue{$plural} detected\">";
	         break;
    }
}

function formatDateDiff($start, $end=null) { 
    if(!($start instanceof DateTime)) { 
        $start = new DateTime($start); 
    } 
    
    if($end === null) { 
        $end = new DateTime(); 
    } 
    
    if(!($end instanceof DateTime)) { 
        $end = new DateTime($start); 
    } 
    
    $interval = $end->diff($start); 
    $doPlural = function($nb,$str){return $nb>1?$str.'s':$str;}; // adds plurals 
    
    $format = array(); 
    if($interval->y !== 0) { 
        $format[] = "%y ".$doPlural($interval->y, "year"); 
    } 
    if($interval->m !== 0) { 
        $format[] = "%m ".$doPlural($interval->m, "month"); 
    } 
    if($interval->d !== 0) { 
        $format[] = "%d ".$doPlural($interval->d, "day"); 
    } 
    if($interval->h !== 0) { 
        $format[] = "%h ".$doPlural($interval->h, "hr"); 
    } 
    if($interval->i !== 0) { 
        $format[] = "%i ".$doPlural($interval->i, "min"); 
    } 
    if($interval->s !== 0) { 
        if(!count($format)) { 
            return "less than a minute ago"; 
        } else { 
            $format[] = "%s ".$doPlural($interval->s, "sec"); 
        } 
    } 
    
    // We use the three biggest parts 
    if(count($format) > 2) { 
        $format = array_shift($format).", ".array_shift($format)." and ".array_shift($format); 
    } else
	if(count($format) > 1) { 
	   $format = array_shift($format).", ".array_shift($format); 
	} else { 
        $format = array_pop($format); 
    } 
    
    // Prepend 'since ' or whatever you like 
    return $interval->format($format); 
}






function formatinterval($val) {

    $doPlural = function($nb,$str){return $nb>1?$str.'s':$str;}; // adds plurals 
		
$d1 = new DateTime();
$d2 = new DateTime();
$d2->add(new DateInterval('PT'.$val.'M'));
      
$interval = $d2->diff($d1);
	
	
//	$interval= new dateinterval('PT'.$val.'M');
	
    $format = array(); 
    if($interval->y !== 0) { 
        $format[] = "%y ".$doPlural($interval->y, "year"); 
    } 
    if($interval->m !== 0) { 
        $format[] = "%m ".$doPlural($interval->m, "month"); 
    } 
    if($interval->d !== 0) { 
        $format[] = "%d ".$doPlural($interval->d, "day"); 
    } 
    if($interval->h !== 0) { 
        $format[] = "%h ".$doPlural($interval->h, "hr"); 
    } 
    if($interval->i !== 0) { 
        $format[] = "%i ".$doPlural($interval->i, "min"); 
    } 
    if($interval->s !== 0) { 
        if(!count($format)) { 
            return "less than 1 minute"; 
        } else { 
            $format[] = "%s ".$doPlural($interval->s, "sec"); 
        } 
    } 
    
    // We use the three biggest parts 
    if(count($format) > 2) { 
        $format = array_shift($format).", ".array_shift($format)." and ".array_shift($format); 
    } else
	if(count($format) > 1) { 
	   $format = array_shift($format).", ".array_shift($format); 
	} else { 
        $format = array_pop($format); 
    } 
    
    // Prepend 'since ' or whatever you like 
    return $interval->format($format); 
}



function formatBytes($size, $precision = 2)
{
    if($size == 0) {
       return 0 . 'b';
    } else
    $base = log($size) / log(1024);
    $suffixes = array('b', ' kB', ' MB', ' GB', ' TB');   
    return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
} 

?>
