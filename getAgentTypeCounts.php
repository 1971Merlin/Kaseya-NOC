<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

$tsql = "select count(distinct userIpInfo.agentGuid) as count, ostype, osInfo, servicePackLevel as sp, buildNumber as build
  from userIpInfo";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = userIpInfo.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
  join dbo.DenormalizedOrgToMach on userIpInfo.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql.=" where OsInfo like '%server%'
  group by ostype, osInfo, servicePackLevel, buildNumber
  order by ostype desc, osInfo desc, buildNumber desc, count desc";
  
$tsql2 = "select count(distinct userIpInfo.agentGuid) as count, ostype, osInfo, servicePackLevel as sp, buildNumber as build, majorVersion, minorVersion
  from userIpInfo";
if ($usescopefilter==true) { $tsql2.=" join vdb_Scopes_Machines foo on (foo.agentGuid = userIpInfo.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql2.=" 
  join dbo.DenormalizedOrgToMach on userIpInfo.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql2.=" where OsInfo not like '%server%'
  group by ostype, osInfo, servicePackLevel, buildNumber, majorVersion, minorVersion
  order by ostype desc, osInfo desc, buildNumber desc, count DESC";
 
?>
<div id="agentdialog"></div>
<script type="text/javascript">
$(document).ready(function() {
$( "#agentdialog" ).dialog({ autoOpen: false, dialogClass: "no-close" });
//$( "#agentdialog" ).dialog({ autoOpen: false, modal: true });
$( "#agentcnts td:first-child, #agentWScnts td:first-child" ).mouseover(function() { 
	$( "#agentdialog" ).dialog("option", "title", $(this).text());
	$.ajax({
		'type':'GET',
		'url': "getAgentTypeCountsPopup.php?name="+$(this).attr('ref'),
		'cache':false,
		'success':function(data) {
			$("#agentdialog").html(data);
		}
		});
//		$("#agentdialog").dialog( "option" ,"position", { collision: "flipfit", within: "#row2" } );
		$("#agentdialog").dialog("option", "minHeight", 50);
		$("#agentdialog").dialog("option", "maxHeight", 350);
//		$("#agentdialog").dialog( "moveToTop" );
		$("#agentdialog").css('overflow', 'none');
		$("#agentdialog").dialog("option", "width", 'auto');
		$("#agentdialog").dialog( "open" );
		$("#agentdialog").dialog( "option" ,"position", { my: "left top", at: "right middle", of: $(this), collision: "fit" } );
//	});
	
	}).mouseout(function() {
	$( "#agentdialog" ).dialog( "close" );
	});
});
</script>
<?php
echo "<div class=\"heading\">";
Echo "Server OS Agent Counts";
echo "</div>";


//* SVR *//
echo "<div class=\"datatable\">";
echo "<table id=\"agentcnts\">";
echo "<tr><th class=\"colL\">OS Name</th><th class=\"colM\">Count</th><th class=\"colM\">Build</th><th class=\"colM\">SP</th></tr>";

$count=0;
$datax = array();
$o0=0;
$o3=0;
$o3r2=0;
$o8=0;
$o8r2=0;
$o12=0;
$o12r2=0;
$linux=0;

$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
	echo "Error in executing query.<br/>";
	die( print_r( sqlsrv_errors(), true));
}

while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
		echo "<tr><td class=\"colL\" ref=\"{$row['osInfo']}\">";
		
	if ($row['ostype'] == "2012") { 
		echo "<img src=\"images/server2012.gif\">";
		if (strpos($row['osInfo'],'R2 ')!==false) { $o12r2+=$row['count']; } else { $o12+=$row['count']; }
	}
	if ($row['ostype'] == "2008") { 
		echo "<img src=\"images/server2008.gif\">";
		if (strpos($row['osInfo'],'R2 ')!==false) { $o8r2+=$row['count']; } else { $o8+=$row['count']; }
	}
	if ($row['ostype'] == "2003") {
		echo "<img src=\"images/server2003.gif\">";
		if (strpos($row['osInfo'],'R2 ')!==false) { $o3r2+=$row['count']; } else { $o3+=$row['count']; }
	}
	if ($row['ostype'] == "2000") { echo "<img src=\"images/win2k.gif\">"; $o0+=$row['count']; }
	if ($row['ostype'] == "Linux") { echo "<img src=\"images/linux.gif\"> Linux "; $linux+=$row['count']; }

	

	$osinfo=$row['osInfo']; 
	$pos=strpos($osinfo,'Build');
	if ($pos!==false) {  $osinfo = substr($osinfo,0,$pos); }	
	$pos=strpos($osinfo,'Service Pack');
	if ($pos!==false) {  $osinfo = substr($osinfo,0,$pos); }
	$osinfo = trim($osinfo);


	echo " Windows ".$row['ostype']." ".$osinfo."</td>";
	echo "<td class=\"colM\">".$row['count']."</td>";
	echo "<td class=\"colM\">".$row['build']."</td>";

	echo "<td class=\"colM\">";
	if ($row['sp']==0) { echo "&nbsp;"; } else { echo $row['sp']; }
	echo "</td></tr>";

	$count=$count+$row['count'];
}




$count2=$count." Server";
if ($count>1){ $count2.="s"; }
$count2.="**";

if ($count > 0) {

echo "<tr><td class=\"colL\" ref=\"$count2\"><b>Total</b></td><td class=\"colM\"><b>$count</b></td></tr>"; 
echo "</table>";
echo "</div>";

if ($o0>0) { $datax[] = "['2000', ".$o0."]"; }
if ($o3>0) { $datax[] = "['2003', ".$o3."]"; }
if ($o3r2>0) { $datax[] = "['2003 R2', ".$o3r2."]"; }
if ($o8>0) { $datax[] = "['2008', ".$o8."]"; }
if ($o8r2>0) { $datax[] = "['2008 R2', ".$o8r2."]"; }
if ($o12>0) { $datax[] = "['2012', ".$o12."]"; }
if ($o12r2>0) { $datax[] = "['2012 R2', ".$o12r2."]"; }
if ($linux>0) {$datax[] = "['Linux', ".$linux."]"; }


echo "<div id=\"serverOsGraph\" style=\"float:right\"></div>";

?>
<script type="text/javascript">
var chartSVR;

$(document).ready(function () {

	Highcharts.setOptions({
global: {
useUTC: false
		},
credits: {
enabled: false
		}
	});	

if (chartSVR) chartSVR.destroy();

chartSVR = new Highcharts.Chart({

chart: {
renderTo: 'serverOsGraph',
type: 'pie',
options3d: {
enabled: false,
alpha: 45,
beta: 0,
},
height: 250,
width: 250,	
margin: [0, 0, 0, 0],
},
tooltip: { enabled: true },

legend: {
enabled: true,
align: 'left',
labelFormat: '<b>{name}</b> ({percentage:.1f}%)',
verticalAlign: 'top',
layout: 'vertical',
symbolHeight: 9,
itemStyle: { fontSize: '9px', fontWeight: 'normal' },
margin: 0,
borderWidth: 1,
borderRadius: 3,
backgroundColor: '#f0f0f0'
},

plotOptions: {
pie: {
center: ['50%','65%'],
animation: false,
depth: 25,
dataLabels: {
connectorPadding: 10,
distance: 15,
enabled: true,
format: '<b>{point.name}</b> : {point.y}',
style: {
fontSize: '9px'
},
},
showInLegend: true,
allowPointSelect: false,
},
},
title: { text: null	},
series: [{
name: 'OS Count',
data: [<?php echo join($datax, ',') ?>],
tooltip: {
headerFormat: '<b>{point.key}</b><br/>',
pointFormat: '{series.name}: {point.y}',
			}
		}]
	})
});
</script>

<?php 
}




//* spacer *//
echo "<div class=\"spacer\"></div>";

//* WS *//
echo "<div class=\"heading heading2\">Workstation OS Agent Counts</div>";


echo "<div class=\"datatable\">";
echo "<table id=\"agentWScnts\">";
echo "<tr><th class=\"colL\">OS Name</th><th class=\"colM\">Count</th><th class=\"colM\">Build</th><th class=\"colM\">SP</th></tr>";

$count=0;
$datax = array();
$xp=0;
$vista=0;
$o7=0;
$o8=0;
$o81=0;
$o10=0;
$osx=0;
$o0=0;
$linux=0;

$stmt2 = sqlsrv_query( $conn, $tsql2);
if( $stmt2 === false )
{
	echo "Error in executing query.<br/>";
	die( print_r( sqlsrv_errors(), true));
}

while( $row = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC))
{

	echo "<tr><td class=\"colL\" ref=\"{$row['osInfo']}\">";

	if ($row['ostype'] == "Mac OS X") { echo "<img src=\"images/macosx.gif\"> ".$row['osInfo']."</td>"; $osx+=$row['count']; }
	else {
		if ($row['ostype'] == "7") { echo "<img src=\"images/win7.gif\"> Windows "; $o7+=$row['count']; }
		if ($row['ostype'] == "8") { echo "<img src=\"images/win8.gif\"> Windows "; $o8+=$row['count']; }
		if ($row['ostype'] == "8.1") { echo "<img src=\"images/win8.gif\"> Windows "; $o81+=$row['count']; }
		if ($row['ostype'] == "10") { echo "<img src=\"images/win10.gif\"> Windows "; $o10+=$row['count']; }
		if ($row['ostype'] == "XP") { echo "<img src=\"images/winxp.gif\"> Windows "; $xp+=$row['count']; }
		if ($row['ostype'] == "Vista") { echo "<img src=\"images/winvista.gif\"> Windows "; $vista+=$row['count']; }
		if ($row['ostype'] == "2000") { echo "<img src=\"images/win2k.gif\"> Windows "; $o0+=$row['count']; }
		if ($row['ostype'] == "Linux") { echo "<img src=\"images/linux.gif\"> "; $linux+=$row['count']; }


		
		$osinfo=$row['osInfo'];  
		$pos=strpos($osinfo,'Build');
		if ($pos!==false) {  $osinfo = substr($osinfo,0,$pos); }
		$pos=strpos($osinfo,'Service Pack');
		if ($pos!==false) {  $osinfo = substr($osinfo,0,$pos); }

		echo $row['ostype']." ".$osinfo."</td>";
	}
	echo "<td class=\"colM\">".$row['count']."</td>";

	echo "<td class=\"colM\">";
	if ($row['ostype']=="Linux") { echo "&nbsp;"; } else {
		if ($row['ostype']=="Mac OS X") { echo $row['majorVersion'].".".$row['minorVersion']."."; }
		echo $row['build']."</td>";
	}
	
	echo "<td class=\"colM\">";
	if ($row['sp']==0) { echo "&nbsp;"; } else { echo $row['sp']; }
	echo "</td></tr>";

	$count=$count+$row['count'];  
}


$count2=$count." Workstation";
if ($count>1){ $count2.="s"; }
$count2.="**";

if ($count > 0) {

  echo "<tr><td class=\"colL\" ref=\"$count2\"><b>Total</b></td><td class=\"colM\"><b>$count</b></td></tr>"; 
  echo "</table>";
  echo "</div>";

if ($osx>0) { $datax[] = "['Mac OS', ".$osx."]"; }
if ($xp>0) {$datax[] = "['Win XP', ".$xp."]"; }
if ($vista>0) {$datax[] = "['Vista', ".$vista."]"; }
if ($o7>0) {$datax[] = "['Win 7', ".$o7."]"; }
if ($o8>0) {$datax[] = "['Win 8', ".$o8."]"; }
if ($o81>0) {$datax[] = "['Win 8.1', ".$o81."]"; }
if ($o10>0) {$datax[] = "['Win 10', ".$o10."]"; }
if ($o0>0) {$datax[] = "['Win 2000', ".$o0."]"; }
if ($linux>0) {$datax[] = "['Linux', ".$linux."]"; }


echo "<div id=\"wsOsGraph\" style=\"float:right;\"></div>";


?>
<script type="text/javascript">
var chartWS; //declare globally

$(document).ready(function () {
	Highcharts.setOptions({
global: {
useUTC: false
		},
credits: {
enabled: false
		}
	});

if (chartWS) chartWS.destroy();

chartWS = new Highcharts.Chart({

chart: {
renderTo: 'wsOsGraph',
type: 'pie',
options3d: {
enabled: false,
alpha: 45,
beta: 0,
			},
height: 250,
width: 320,	
margin: [0, 0, 0, 0],
		},
		
tooltip: { enabled: true },
		
legend: {
enabled: true,
align: 'left',
labelFormat: '<b>{name}</b> ({percentage:.1f}%)',
verticalAlign: 'top',
layout: 'vertical',
symbolHeight: 9,
itemStyle: { fontSize: '9px', fontWeight: 'normal' },
margin: 0,
borderWidth: 1,
borderRadius: 3,
backgroundColor: '#f0f0f0'
		},

plotOptions: {
pie: {
center: ['65%','65%'],
animation: false,
depth: 25,
dataLabels: {
connectorPadding: 10,
distance: 15,
enabled: true,
format: '<b>{point.name}</b> : {point.y}',
style: {
fontSize: '9px'
					},
				},
showInLegend: true,
allowPointSelect: false,
			},
		},

title: { text: null	},
		
series: [{
name: 'OS Count',
data: [<?php echo join($datax, ',') ?>],
tooltip: {
headerFormat: '<b>{point.key}</b><br/>',
pointFormat: '{series.name}: {point.y}',
			}			
		}]
	})
});

</script>
<?php 
}

sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>