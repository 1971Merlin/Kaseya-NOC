<!DOCTYPE html>
<!-- <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> -->
<html>
<head>
<title>NOC Configuration</title>
<link rel="stylesheet" type="text/css" href="main.css">
<script>
function validateForm()
{
var x=document.forms["configure"]["serverName"].value;
if (x==null || x=="")
  {
  alert("SQL Server Name cannot be blank!");
  return false;
  }
var x=document.forms["configure"]["user"].value;
if (x==null || x=="")
  {
  alert("SQL Username cannot be blank!");
  return false;
  }
var x=document.forms["configure"]["password"].value;
if (x==null || x=="")
  {
  alert("SQL Password cannot be blank!");
  return false;
  }

}
</script>
</head>
<body>
<?php
// are we pressing SUBMIT ? //
if (isset($_POST['updatesettings'])){

 echo "Saving Settings..."; 
  
 // save values //
 $config['server']['SQLserver']=$_POST['serverName'];
 $config['connectionInfo']['UID']=$_POST['user'];
 $config['connectionInfo']['PWD']=$_POST['password'];
 $config['connectionInfo']['Database']="Ksubscribers";

// title of mainpage // 
 
 if (isset($_POST['NOCtitle'])) { $config['config']['NOCtitle']=$_POST['NOCtitle']; } else { $config['config']['NOCtitle']="NOC"; }
 

 // scope and org // 
 
if (isset($_POST['scope'])) $config['config']['scope_filter']=$_POST['scope']; else $config['config']['scope_filter']="Master";
if (isset($_POST['org']))  $config['config']['org_filter']=$_POST['org']; else  $config['config']['org_filter']="Master";


// alarm on/off

 if (isset($_POST['al'])) $alarm="1"; else $alarm="0";
 $config['config']['alarm']=$alarm;


// date format 

 if (isset($_POST['dfmt1'])) $dfmt1=$_POST['dfmt1']; else $dfmt1="d";
 $config['config']['dfmt1']=$dfmt1;

 if (isset($_POST['dfmt1'])) $dsep=$_POST['dsep']; else $dsep="/";
 $config['config']['dsep']=$dsep;
 
 if (isset($_POST['dfmt2'])) $dfmt2=$_POST['dfmt2']; else $dfmt2="m";
 $config['config']['dfmt2']=$dfmt2;

 if (isset($_POST['dfmt3'])) $dfmt3=$_POST['dfmt3']; else $dfmt3="y";
 $config['config']['dfmt3']=$dfmt3;
 
// time format
 
 if (isset($_POST['tsep'])) $tsep=$_POST['tsep']; else $tsep="/";
 $config['config']['tsep']=$tsep;
 
 if (isset($_POST['tampm'])) $tampm=$_POST['tampm']; else $tampm="A";
 $config['config']['tampm']=$tampm;
  
 

 if (isset($_POST['resultcount'])) $rescnt=$_POST['resultcount']; else $rescnt=10;
 $config['config']['resultCount']=$rescnt;

 
 
// top strip panels on/off

 if (isset($_POST['pendApprove'])) $config['strip']['showPendApprove']="1"; else $config['strip']['showPendApprove']="0"; 
 if (isset($_POST['oldAgents'])) $config['strip']['showOldAgents']="1"; else $config['strip']['showOldAgents']="0"; 
 if (isset($_POST['olServers'])) $config['strip']['showOlServers']="1"; else $config['strip']['showOlServers']="0"; 
 if (isset($_POST['Ungrouped'])) $config['strip']['showUngrouped']="1"; else $config['strip']['showUngrouped']="0";
 if (isset($_POST['PendingReboot'])) $config['strip']['showPendReboot']="1"; else $config['strip']['showPendReboot']="0";
 if (isset($_POST['Suspended'])) $config['strip']['showSuspended']="1"; else $config['strip']['showSuspended']="0";
 if (isset($_POST['Mobile'])) $config['strip']['showMobile']="1"; else $config['strip']['showMobile']="0";
 if (isset($_POST['olGraph'])) $config['strip']['showOlGraph']="1"; else $config['strip']['showOlGraph']="0";
 if (isset($_POST['RCHistory'])) $config['strip']['showRCHistory']="1"; else $config['strip']['showRCHistory']="0";
 if (isset($_POST['VSAUsers'])) $config['strip']['showVSAUsers']="1"; else $config['strip']['showVSAUsers']="0";
 if (isset($_POST['showEXT'])) $config['strip']['showEXT']="1"; else $config['strip']['showEXT']="0";
 if (isset($_POST['showRSS'])) $config['strip']['showRSS']="1"; else $config['strip']['showRSS']="0";
 if (isset($_POST['extURL'])) $config['strip']['extURL']=$_POST['extURL']; else { $config['strip']['showEXT']="0"; $config['strip']['extURL']=""; }
 if (isset($_POST['rssURL'])) $config['strip']['rssURL']=$_POST['rssURL']; else { $config['strip']['showRSS']="0"; $config['strip']['rssURL']=""; }
 if (isset($_POST['secstats'])) $config['strip']['showSecurity']="1"; else $config['strip']['showSecurity']="0";
 if (isset($_POST['avstats'])) $config['strip']['showAV']="1"; else $config['strip']['showAV']="0";
 if (isset($_POST['budrstats'])) $config['strip']['showBUDR']="1"; else $config['strip']['showBUDR']="0";
 if (isset($_POST['polstats'])) $config['strip']['showPolicy']="1"; else $config['strip']['showPolicy']="0";
 if (isset($_POST['checkin'])) $config['strip']['showLastCheckin']="1"; else $config['strip']['showLastCheckin']="0";
 if (isset($_POST['scripts'])) $config['strip']['showScripts']="1"; else $config['strip']['showScripts']="0";
 

 
 
// fix RSS field blank?
 if ($config['strip']['rssURL']=='') { $config['strip']['showRSS']="0"; }
 
// fix URL field blank?
 if ($config['strip']['extURL']=='') { $config['strip']['showEXT']="0"; }

 
 
// main panels on/off 

 if (isset($_POST['Counts'])) $config['panels']['showCounts']="1"; else $config['panels']['showCounts']="0"; 
 if (isset($_POST['Policy'])) $config['panels']['showPolicy']="1"; else $config['panels']['showPolicy']="0"; 
 if (isset($_POST['SD'])) $config['panels']['showSD']="1"; else $config['panels']['showSD']="0";
 if (isset($_POST['MGS'])) $config['panels']['showMGS']="1"; else $config['panels']['showMGS']="0";
 if (isset($_POST['budr'])) $config['panels']['showBUDR']="1"; else $config['panels']['showBUDR']="0";
 if (isset($_POST['av'])) $config['panels']['showAv']="1"; else $config['panels']['showAv']="0";
 if (isset($_POST['KAV'])) $config['panels']['showKAV']="1"; else $config['panels']['showKAV']="0";
 if (isset($_POST['SEP'])) $config['panels']['showSEP']="1"; else $config['panels']['showSEP']="0";
 if (isset($_POST['alarm'])) $config['panels']['showAlarms']="1"; else $config['panels']['showAlarms']="0";
 if (isset($_POST['uptime'])) $config['panels']['showUptime']="1"; else $config['panels']['showUptime']="0";
 if (isset($_POST['patching'])) $config['panels']['showPatching']="1"; else $config['panels']['showPatching']="0";
 if (isset($_POST['lowdisk'])) $config['panels']['showLowDisk']="1"; else $config['panels']['showLowDisk']="0";
 if (isset($_POST['RCinfo'])) $config['panels']['showRC']="1"; else $config['panels']['showRC']="0";
 if (isset($_POST['SEC'])) $config['panels']['showSEC']="1"; else $config['panels']['showSEC']="0";

 
 $cache_file = $_SERVER['DOCUMENT_ROOT'].'/rsscache.rss';
 if ($config['strip']['showRSS']==1 && file_exists($cache_file)) { unlink($cache_file); }
 
  

 // actual save here //
  
 write_php_ini($config, 'noc.ini');
 echo "Done!!"; 
 echo "<script>window.location = 'index.php'</script>";
 die();
}


// OK not submitting, so must draw initial page //
// read the ini file //
  $config = parse_ini_file("noc.ini", true);
  $serverName = $config['server']['SQLserver'];
  $connectionInfo = $config['connectionInfo'];
  $connectionInfo["LoginTimeout"] = 5;
  

// hardcode Ksubscribers database name //  
  if ($connectionInfo['Database'] == "") {
    $connectionInfo['Database'] = 'Ksubscribers';
	$config['connectionInfo']['Database'] = 'Ksubscribers';
}
	
// check for missing noc.ini settings


// row1 : top strip
if (!isset($config['strip']['showPendApprove'])) { $config['strip']['showPendApprove'] = true; }
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
if (!isset($config['strip']['showLastCheckin'])) { $config['strip']['showLastCheckin'] = true; }
if (!isset($config['strip']['extURL'])) { $config['strip']['extURL'] = ""; }
if (!isset($config['strip']['rssURL'])) { $config['strip']['rssURL'] = ""; }
if (!isset($config['strip']['showScripts'])) { $config['strip']['showScripts'] = true; }



// row2 : panels

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
if (!isset($config['panels']['showSEC'])) { $config['panels']['showSEC'] = true; }


// date & time formatting parameters

if (!isset($config['config']['dfmt1'])) { $config['config']['dfmt1'] = "d"; }
if (!isset($config['config']['dsep'])) { $config['config']['dsep'] = "-"; }
if (!isset($config['config']['dfmt2'])) { $config['config']['dfmt2'] = "m"; }
if (!isset($config['config']['dfmt3'])) { $config['config']['dfmt3'] = "Y"; }
if (!isset($config['config']['tsep'])) { $config['config']['tsep'] = ":"; }
if (!isset($config['config']['tampm'])) { $config['config']['tampm'] = "a"; }

// resultcount

if (!isset($config['config']['resultCount'])) { $config['config']['resultCount'] = 12; }


// alert sounds on/off

if (!isset($config['config']['alarm'])) { $config['config']['alarm'] = true; }

	
	
?>
<div id="topheading" name="topheading">NOC Configuration</div>
<div id="inputform" name="inputform">
<form name="configure" action="editsettings.php" method="POST" onsubmit="return validateForm();">
<fieldset>
<legend>SQL Server Connection</legend>
<label for="serverName">SQL Server Name</label>
<input type="text" name="serverName" value="<?php echo $config['server']['SQLserver'];?>">
<br/>
<label for="user">SQL Username</label>
<input type="text" name="user" value="<?php echo $config['connectionInfo']['UID'];?>">
<br/>
<label for="password">SQL Password</label>
<input type="password" name="password" value="<?php echo $config['connectionInfo']['PWD'];?>">
<br/>
<label for="database">SQL Database</label>
<input type="text" name="database" value="<?php echo $config['connectionInfo']['Database'];?>" readonly style="background-color:#eeeeee;">
<br/>
</fieldset>

<fieldset>
<legend>NOC Title</legend>
<label for="NOCtitle">Mainpage NOC Title</label>
<input type="text" name="NOCtitle" size="40" maxlength="40" value="<?php echo $config['config']['NOCtitle'];?>">
<br/>
</fieldset>

<?php
$tsql = "SELECT ref from adminScope where internalCode = 0 order by ref";
$connectionInfo["LoginTimeout"] = 5;

$conn = sqlsrv_connect( $serverName, $connectionInfo);
if( $conn === false ) {
     echo "Please configure servername, username and password before configuring Scope and Org!<br/>";
	 $dbok=false;
  } else {
  $dbok=true;
  echo "<fieldset>";
  echo "<legend>Scope and ORG Selection</legend>";

  if (isset($_GET['alert'])) {
    echo "<font color=red><b>*** Please select one option from the following list</b></font><br/>";
  }
   
  $stmt = sqlsrv_query( $conn, $tsql);
  if( $stmt === false ) {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }
  echo "<div style=\"max-height:200px;overflow:auto; float:left;\">";
  echo "<table class=\"orgscope\">";

 // * SCOPE *//
  
 //* master *//
  echo "<tr><th class=\"colL\"> </th><th class=\"colL\">Scope Name</th></tr>";
  echo "<tr><td><input type=\"radio\" name=\"scope\" value=\"Master\"";
   if ($config['config']['scope_filter']=='Master') { echo " checked"; }
  echo "></td><td>Master View</td></tr>";

//* others *//
  while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC)) {
    echo "<tr><td><input type=\"radio\" name=\"scope\" value=\"".$row['ref']."\"";
	if ($config['config']['scope_filter']==$row['ref']) { echo " checked"; }
	echo "></td><td>".$row['ref']."</td></tr>";
  }
  echo "</table>";
  echo "</div>";

//* ORG *//



//* where orgType !='kserver' *//
  
$tsql = "SELECT ref, orgName from kasadmin.org order by ref";

$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}



  
  echo "<div style=\"max-height:200px;overflow:auto;\">";
  echo "<table class=\"orgscope\">";

 //* master *//
  echo "<tr><th class=\"colL\"> </th><th class=\"colL\">Organization Name</th></tr>";
  echo "<tr><td><input type=\"radio\" name=\"org\" value=\"Master\"";
   if ($config['config']['org_filter']=='Master') { echo " checked"; }
  echo "></td><td>Master View</td></tr>";

//* others *//
  while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC)) {
    echo "<tr><td><input type=\"radio\" name=\"org\" value=\"".$row['ref']."\"";
	if ($config['config']['org_filter']==$row['ref']) { echo " checked"; }
	echo "></td><td>".$row['orgName']."</td></tr>";
  }
  
  
  echo "</table>";
  echo "</div>";
  
  echo "</fieldset>";
  }
?>




<fieldset>
<legend>NOC Panel Display Options</legend>
<label for="alarm">Offline Alarm Sound</label>
<input type="checkbox" name="al" <?php if($config['config']['alarm']==true){echo "checked";} ?>><br/>

<label for="resultcount">Limit to first N Items</label>
<input type="number" name="resultcount" min="0" max ="99" size="2" value="<?php echo $config['config']['resultCount'];?>"><br/>


<label for="dfmt1">Date Format</label>
<select name="dfmt1">
<option value="d" <?php if($config['config']['dfmt1']=="d"){echo "selected";}?>>day</option>
<option value="m" <?php if($config['config']['dfmt1']=="m"){echo "selected";}?>>mth</option>
<option value="M" <?php if($config['config']['dfmt1']=="M"){echo "selected";}?>>Month</option>
<option value="y" <?php if($config['config']['dfmt1']=="y"){echo "selected";}?>>yr</option>
<option value="Y" <?php if($config['config']['dfmt1']=="Y"){echo "selected";}?>>year</option>
</select>

<select name="dsep">
<option value="/" <?php if($config['config']['dsep']=="/"){echo "selected";}?>>/</option>
<option value="-" <?php if($config['config']['dsep']=="-"){echo "selected";}?>>-</option>
<option value=" " <?php if($config['config']['dsep']==" "){echo "selected";}?>> </option>
</select>

<select name="dfmt2">
<option value="d" <?php if($config['config']['dfmt2']=="d"){echo "selected";}?>>day</option>
<option value="m" <?php if($config['config']['dfmt2']=="m"){echo "selected";}?>>mth</option>
<option value="M" <?php if($config['config']['dfmt2']=="M"){echo "selected";}?>>Month</option>
<option value="y" <?php if($config['config']['dfmt2']=="y"){echo "selected";}?>>yr</option>
<option value="Y" <?php if($config['config']['dfmt2']=="Y"){echo "selected";}?>>year</option>
</select>

<select name="dfmt3">
<option value="d" <?php if($config['config']['dfmt3']=="d"){echo "selected";}?>>day</option>
<option value="m" <?php if($config['config']['dfmt3']=="m"){echo "selected";}?>>mth</option>
<option value="M" <?php if($config['config']['dfmt3']=="M"){echo "selected";}?>>Month</option>
<option value="y" <?php if($config['config']['dfmt3']=="y"){echo "selected";}?>>yr</option>
<option value="Y" <?php if($config['config']['dfmt3']=="Y"){echo "selected";}?>>year</option>
</select>
<br/>

<label for="tsep">Time Format</label>
09
<select name="tsep">
<option value=":" <?php if($config['config']['tsep']==":"){echo "selected";}?>>:</option>
<option value="." <?php if($config['config']['tsep']=="."){echo "selected";}?>>.</option>
</select>
30
<select name="tampm" onchange="rfr()">
<option value="a" <?php if($config['config']['tampm']=="a"){echo "selected";}?>>am/pm</option>
<option value="A" <?php if($config['config']['tampm']=="A"){echo "selected";}?>>AM/PM</option>
<option value="" <?php if($config['config']['tampm']==""){echo "selected";}?>>(24hr)</option>
</select>
<br/>
</fieldset>













<?php
// * get Licensed apps * //

  $lic_data = array();

  
  if ($dbok == true ) {
    $tsql = "select id, ref, version from vdb_LicenseApps";
    $stmt = sqlsrv_query( $conn, $tsql);
    if( $stmt === false ) {
       echo "Error in executing query.<br/>";
       die( print_r( sqlsrv_errors(), true));
    }
    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC)) {
      $lic_data[]=$row;
    }
  } 
  function findProduct($products, $needle) {
    foreach($products as $key => $product) {
      if ( $product['id'] === $needle )
        return $key;
      }
    return null;
  } 
  

/*
id	ref						version
0	Kaseya System Patch		8.0.0.11
3	Agent Procedures		~AddonVersion~
6	Patch Management		8.0.0.0
12	Backup					8.0.0.0 (KBU aka BUDR aka Acronis)
15	KES						8.0.0.0 (KES aka AVG)
18	Service Desk			8.0.0.0
29	Desktop Management: Migration	8.0.0.0
30	Desktop Management: Policy	8.0.0.0
31	Network Discovery		1.2.0.0
36	Anti-Virus				8.0.0.0 (KAV aka Kaspersky)
41	Time Tracking			8.0.0.0
42	Service Billing			8.0.0.0
44	Policy					8.0.0.0
46	Anti-Malware			8.0.0.0 (KAM aka Malwarebytes)
47	Network Monitoring		8.0.0.0 (KNMi)
50	KMDM					8.0.0.0 (Mobile management)
60	KSBR					
70	Discovery				8.0.0.0 (KND)
85	vPro Management			8.0.0.0
95	Anti-Virus ( new to 9.3)
97	Anti-Malware ( new to 9.3)
115	AuthAnvil
134	Veeam Backup &amp; Replication	1.0.0.0
135	Symantec Integration	2.5.1.13 (SEP aka Symantec Endpoint Security)

*/

?>

<script type="text/javascript">
function toggle(className,parentState){
                var state = document.getElementById(parentState).checked;
                var menus = document.getElementsByClassName(className);
                for (var i = menus.length - 1; i >= 0; i--)
                {
				  if ( menus[i].disabled == false ) {
				    menus[i].checked=state;
				  }
                }
}
</script>


<fieldset>
<legend>Core Panels Enable / Disable</legend>
<table>
<tr><td>
  <label for="toggle3">Enable/Disable All</label>
  <input type="checkbox" name="toggle3" id="toggle3" onclick="toggle('list3','toggle3')">
</td></tr>
<tr><td>
 <label for="olServers">Offline Servers</label>
 <input type="checkbox" name="olServers" class="list3" <?php if($config['strip']['showOlServers']==true){echo "checked";} ?>>
</td><td>
<label for="Ungrouped">Ungrouped Agents</label>
<input type="checkbox" name="Ungrouped" class="list3" <?php if($config['strip']['showUngrouped']==true){echo "checked";} ?>>
</td><td>
<label for="PendingReboot">Pending Reboot</label>
<input type="checkbox" name="PendingReboot" class="list3" <?php if($config['strip']['showPendReboot']==true){echo "checked";} ?>>
</td><td>
<label for="Suspended">Suspended Agents</label>
<input type="checkbox" name="Suspended" class="list3" <?php if($config['strip']['showSuspended']==true){echo "checked";} ?>>
</td></tr>
<tr><td>
<label for="Mobile">Mobile Issues</label>
<input type="checkbox" name="Mobile" class="list3" <?php if($config['strip']['showMobile']==true){echo "checked";} if (getKVer($serverName, $connectionInfo) > 8.0 ) {echo " disabled";} ?>>
</td><td>
 <label for="pendApprove">Scripts Pending</label>
 <input type="checkbox" name="pendApprove" class="list3" <?php if($config['strip']['showPendApprove']==true){echo "checked";} if (getKVer($serverName, $connectionInfo) < 7.0 ) {echo " disabled";} ?>>
</td><td>
<label for="oldAgents">Outdated Agents</label>
<input type="checkbox" name="oldAgents" class="list3" <?php if($config['strip']['showOldAgents']==true){echo "checked";} ?>>
</td><td>
<label for="checkin">Agents Not Checking-in</label>
<input type="checkbox" name="checkin" class="list3" <?php if($config['strip']['showLastCheckin']==true){echo "checked";} ?>>
</td></tr>
<tr><td>
<label for="scripts">Running Scripts</label>
<input type="checkbox" name="scripts" class="list3" <?php if($config['strip']['showScripts']==true){echo "checked";} ?>>
</td></tr>
</table>
</fieldset>



<fieldset>
<legend>Other Top Row Panels Enable / Disable</legend>
<table>
<tr><td>
  <label for="toggle1">Enable/Disable All</label>
  <input type="checkbox" name="toggle1" id="toggle1" onclick="toggle('list1','toggle1')">
</td></tr>

<tr><td>
<label for="polstats">Policy Stats</label>
<input type="checkbox" name="polstats" class="list1" <?php if($config['strip']['showPolicy']==true){echo "checked";} ?>>
</td><td>
<label for="budrstats">Backup (KBU) Stats</label>
<?php $res=findProduct($lic_data,12);?>
<input type="checkbox" name="budrstats" class="list1" <?php if($config['strip']['showBUDR']==true){echo "checked";} if ($res==null) {echo " disabled";} ?>>
</td><td>
<label for="olGraph">Agents Online History</label>
<input type="checkbox" name="olGraph" class="list1" <?php if($config['strip']['showOlGraph']==true){echo "checked";} ?>>
</td><td>
<label for="RCHistory">RC History</label>
<input type="checkbox" name="RCHistory" class="list1" <?php if($config['strip']['showRCHistory']==true){echo "checked";} ?>>
</td></tr>
<tr><td>
<label for="VSAUsers">Active VSA Users</label>
<input type="checkbox" name="VSAUsers" class="list1" <?php if($config['strip']['showVSAUsers']==true){echo "checked";} ?>>
</td><td>
<label for="secstats">Security (KES) Stats</label>
<?php $res=findProduct($lic_data,15);?>
<input type="checkbox" name="secstats" class="list1" <?php if($config['strip']['showSecurity']==true){echo "checked";} if ($res==null) {echo " disabled";} ?>>
</td><td>
<label for="secstats">Anti-Virus (Classic)</label>
<?php $res=findProduct($lic_data,36);?>
<input type="checkbox" name="avstats" class="list1" <?php if($config['strip']['showAV']==true){echo "checked";} if ($res==null) {echo " disabled";} ?>>
</td>
</tr>

</table>


<label for="VSAUsers">External URL</label>
<input type="checkbox" name="showEXT" class="list1" <?php if($config['strip']['showEXT']==true){echo "checked";} ?>>
<input type="text" name="extURL" size="80" maxlength="255" value="<?php echo $config['strip']['extURL'];?>">
<br/>
<label for="VSAUsers">External RSS Feed</label>
<input type="checkbox" name="showRSS" class="list1" <?php if($config['strip']['showRSS']==true){echo "checked";} ?>>
<input type="text" name="rssURL" size="80" maxlength="255" value="<?php echo $config['strip']['rssURL'];?>">
<br/>


</fieldset>

<fieldset>
<legend>NOC Panels Enable / Disable</legend>

<table class="modules">
<tr><th class="colL"></th><th class="colL">K Module Detected</th><th class="colM">Module Version</th></tr>

<tr><td>
  <label for="toggle2">Enable/Disable All</label>
  <input type="checkbox" name="toggle2" id='toggle2' onclick="toggle('list2','toggle2')">
<td></tr>

<tr><td>
<label for="lowdisk">RC Details</label>
<input type="checkbox" name="RCinfo" class='list2' <?php if($config['panels']['showRC']==true){echo "checked";} ?>>
</td><td>VSA Core</td><td></td></tr>

<tr><td>
<label for="SD">Agent Counts</label>
<input type="checkbox" name="Counts" class='list2' <?php if($config['panels']['showCounts']==true){echo "checked";} ?>>
</td><td>VSA Core</td><td></td></tr>

<tr><td>
<label for="SD">Policy</label>
<?php $res=findProduct($lic_data,44);?>
<input type="checkbox" name="Policy" class='list2' <?php if($config['panels']['showPolicy']==true and $res!=null) {echo "checked";} if ($res==null) {echo " disabled";} ?>>
</td><td>
<?php if ($res!=null) { echo $lic_data[$res]['ref']."</td><td class=\"colM\">".$lic_data[$res]['version']; }  else { echo 'Not Installed</td><td>'; } ?>
</td></tr>

<tr><td>
<label for="SD">Service Desk</label>
<?php $res=findProduct($lic_data,18);?>
<input type="checkbox" name="SD" class='list2' <?php if($config['panels']['showSD']==true){echo "checked";} if ($res==null) {echo " disabled";} ?>>
</td><td>
<?php if ($res!=null) { echo $lic_data[$res]['ref']."</td><td class=\"colM\">".$lic_data[$res]['version']; }  else { echo 'Not Installed</td><td>'; } ?>
</td></tr>

<tr><td>
<label for="uptime">Server Uptimes</label>
<input type="checkbox" name="uptime" class='list2' <?php if($config['panels']['showUptime']==true){echo "checked";} ?>>
</td><td>VSA Core</td><td></td></tr>

<tr><td>
<label for="MGS">Machine Group Status</label>
<input type="checkbox" name="MGS" class='list2' <?php if($config['panels']['showMGS']==true){echo "checked";} ?>>
</td><td>VSA Core</td><td></td></tr>

<tr><td>
<label for="alarms">Alarms</label>
<input type="checkbox" name="alarm" class='list2' <?php if($config['panels']['showAlarms']==true){echo "checked";} ?>>
</td><td>VSA Core</td><td></td></tr>

<tr><td>
<label for="patch">Patch Management</label>
<?php $res=findProduct($lic_data,6);?>
<input type="checkbox" name="patching" class='list2' <?php if($config['panels']['showPatching']==true){echo "checked";} if ($res==null) {echo " disabled";} ?>>
</td><td>
<?php if ($res!=null) { echo $lic_data[$res]['ref']."</td><td class=\"colM\">".$lic_data[$res]['version']; }  else { echo 'Not Installed</td><td>'; } ?>
</td></tr>

<tr><td>
<label for="lowdisk">Low Disk Space</label>
<input type="checkbox" name="lowdisk" class='list2' <?php if($config['panels']['showLowDisk']==true){echo "checked";} ?>>
</td><td>VSA Core</td><td></td></tr>

<tr><td>
<label for="avg">Security (KES)</label>
<?php $res=findProduct($lic_data,15);?>
<input type="checkbox" name="av" class='list2' <?php if($config['panels']['showAv']==true){echo "checked";} if ($res==null) {echo " disabled";} ?>>
</td><td>
<?php if ($res!=null) { echo $lic_data[$res]['ref']."</td><td class=\"colM\">".$lic_data[$res]['version']; }  else { echo 'Not Installed</td><td>'; } ?>
</td></tr>

<tr><td>
<label for="KAV">Anti-Virus (Classic)</label>
<?php $res=null; $res=findProduct($lic_data,36);?>
<input type="checkbox" name="KAV" class='list2' <?php if($config['panels']['showKAV']==true and $res!=null){echo "checked";} if ($res==null) {echo " disabled";} ?>>
</td><td>
<?php if ($res!=null) { echo $lic_data[$res]['ref']."</td><td class=\"colM\">".$lic_data[$res]['version']; }  else { echo 'Not Installed</td><td>'; } ?>
</td></tr>
<tr><td>
<label for="SEC">Antivirus (9.3+)</label>
<?php $res=null; $res=findProduct($lic_data,95);?>
<input type="checkbox" name="SEC" class='list2' <?php if($config['panels']['showSEC']==true){echo "checked";} if ($res==null) {echo " disabled";} ?>>
</td><td>
<?php if ($res!=null) { echo $lic_data[$res]['ref']."</td><td class=\"colM\">".$lic_data[$res]['version']; }  else { echo 'Not Installed</td><td>'; } ?>
</td><td></td></tr>


<tr><td>
<label for="SEP">Symantec EPP</label>
<?php $res=null; $res=findProduct($lic_data,135);?>
<input type="checkbox" name="SEP" class='list2' <?php if($config['panels']['showSEP']==true){echo "checked";} if ($res==null) {echo " disabled";} ?>>
</td><td>
<?php if ($res!=null) { echo $lic_data[$res]['ref']."</td><td class=\"colM\">".$lic_data[$res]['version']; }  else { echo 'Not Installed</td><td>'; } ?>
</td></tr>
<tr><td>
<label for="budr">Backup (KBU)</label>
<?php $res=null; $res=findProduct($lic_data,12);?>
<input type="checkbox" name="budr" class='list2' <?php if($config['panels']['showBUDR']==true){echo "checked";} if ($res==null) {echo " disabled";} ?>>
</td><td>
<?php if ($res!=null) { echo $lic_data[$res]['ref']."</td><td class=\"colM\">".$lic_data[$res]['version']; }  else { echo 'Not Installed</td><td>'; } ?>
</td><td></td></tr>




</table>
</fieldset>

<input name="updatesettings" type="submit" value="Submit" />
<input type="button" value="Cancel" onClick="history.go(-1);return true;">
</form>
</div>
<div class="spacer"></div>
<?php
function write_php_ini($array, $file) {
    $res = array();
    foreach($array as $key => $val) {
        if(is_array($val)) {
            $res[] = "[$key]";
            foreach($val as $skey => $sval) $res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
        }
        else $res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
    }
    safefilerewrite($file, implode("\r\n", $res));
}

  function safeFileRewrite($filename, $data) {
        $fp = fopen($filename, 'w');
		if ($fp) {
            $start_time = microtime();
            do {
                $can_write = flock($fp, LOCK_EX);
                // If lock not obtained sleep for 0 - 100 milliseconds
                // , to avoid collision and CPU load
                if(!$can_write) usleep(round(rand(0, 100)*1000));
            } while ((!$can_write)and((microtime()-$start_time) < 1000));

            // file was locked so now we can store information
            if ($can_write) {
                fwrite($fp, $data);
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }
    }
?>
</body>
</html>
<?php
// detect server version

function getKVer($serverName, $connectionInfo) {
$connectionInfo['LoginTimeout'] = 5;
$conn = sqlsrv_connect( $serverName, $connectionInfo);
if( $conn === false ) { return "6.0"; }

$tsql = "select paramValue as version from kserverParams where paramName = 'version'";

$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false ) {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);

$kver = $row['version'];
$kver = substr($kver,0,strrpos($kver,'.'));
$kver = substr($kver,0,strrpos($kver,'.'));

return $kver;
sqlsrv_close( $conn );
}
?>