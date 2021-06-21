<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';


$NPP = $_SESSION['NPP'];


//* patch policy members *//

$tsql = "SELECT distinct top ".$resultcount." vl.displayName as machName,
( STUFF((select ', ' + PolicyName from vPatchPolicyMember as st2
  where vppm.machineId = st2.MachineId
  order by st2.PolicyName
  FOR XML PATH(''), TYPE).value('.', 'NVARCHAR(MAX)'), 1, 2, '')
 ) PolicyName,
  missingApproved,pending,failed,
  rebootPending = case
    when rebootPending=0 then 'No'
    else 'Yes'
  end,
  lastPatchScan,testStatusDescription, vl.online as online, vl.currentLogin
  FROM vPatchPolicyMember as vppm";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = vppm.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on vppm.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql.=" join vAgentLabel vl on vl.agentGuid = vppm.agentGuid
  join vPatchStatusByAgent pp on pp.agentGuid = vppm.agentGuid
  where PolicyName not like '-No policy-' and (missingApproved >0 or failed >0)
  order by missingApproved desc, rebootPending desc,  failed desc,  pending desc";

$tsql2 = "SELECT distinct count (agentGuid) as Count, Category FROM vPatchPieChartCountsUsePolicy where osinformation like '%server%'
 group by category order by Count asc";

$tsql3 = "SELECT distinct count (agentGuid) as Count, Category FROM vPatchPieChartCountsUsePolicy where osinformation not like '%server%'
 group by category order by Count asc";
 
 
 $tsql4 = "SELECT sum(totalPatches) as tp, sum(missingApproved) as mp, count(vppm.agentGuid) as num
  FROM vPatchPolicyMember as vppm
  join vPatchStatusByAgent pp on pp.agentGuid = vppm.agentGuid
 where PolicyName not like '-No policy-' and pp.OSInformation like '%server%'";

 $tsql5 = "SELECT sum(totalPatches) as tp, sum(missingApproved) as mp, count(vppm.agentGuid) as num
  FROM vPatchPolicyMember as vppm
  join vPatchStatusByAgent pp on pp.agentGuid = vppm.agentGuid
 where PolicyName not like '-No policy-' and pp.OSInformation not like '%server%'";


?>
<div id="patchdialog"></div>
<script type="text/javascript">
$(document).ready(function() {
$( "#patchdialog" ).dialog({ autoOpen: false, dialogClass: "no-close" });
$( "#patchlist td:first-child" ).mouseover(function() { 
	$( "#patchdialog" ).dialog("option", "title", $(this).text());
	$.ajax({
		'type':'GET',
		'url': "getPatchInfoPopup.php?name="+$(this).attr('ref'),
		'cache':false,
		'success':function(data) {
			$("#patchdialog").html(data);
		}
	});
	$("#patchdialog").dialog("option", "minHeight", 50);
	$("#patchdialog").dialog("option", "maxHeight", 200);
	$('#patchdialog').css('overflow', 'auto');
	$("#patchdialog").dialog("option", "width", 'auto');
	$( "#patchdialog" ).dialog( "open" );
	$( "#patchdialog" ).dialog({ position: { my: "left top", at: "right middle", of: $(this), collision: "fit" } });
	}).mouseout(function() {
		$( "#patchdialog" ).dialog( "close" );
	});
});
</script>
<?php
  

  
 //start chart
 
  
  $datax = array();
  $datay = array();
  
$stmt2 = sqlsrv_query( $conn, $tsql2);
if( $stmt2 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$stmt3 = sqlsrv_query( $conn, $tsql3);
if( $stmt3 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}


$stmt4 = sqlsrv_query( $conn, $tsql4);
if( $stmt4 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}


$stmt5 = sqlsrv_query( $conn, $tsql5);
if( $stmt5 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$colorlist = array();


while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC))
{ 
  $foo = $row2['Category'];
  $foo = str_replace('Missing Patches: 0','Fully Patched',$foo);
  $foo = str_replace('Missing Patches:','Missing:',$foo);
  $datax[] = "['".$foo."',".$row2['Count']."]";


  switch ($foo) {
	  

        case 'OS Not Supported' : $colorlist[] = '#000066'; break;	// blue
		case 'Not Scanned' : $colorlist[] = '#c2c2a3'; break;		// grey
		case 'Missing: 6 or more' : $colorlist[] = '#ff0000'; break;	// red
		case 'Missing: 3 - 5' : $colorlist[] = '#ff6600'; break;	// orange
		case 'Missing: 1 - 2' : $colorlist[] = '#ffff00'; break;	// yellow
		case 'Fully Patched' : $colorlist[] = '#009900'; break;		// green
			}



}

			

$colorlist2 = array();			
			
while( $row3 = sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC))
{ 
  $foo = $row3['Category'];
  $foo = str_replace('Missing Patches: 0','Fully Patched',$foo);
  $foo = str_replace('Missing Patches:','Missing:',$foo);
  $datay[] = "['".$foo."',".$row3['Count']."]";
  
 switch ($foo) {
	  

        case 'OS Not Supported' : $colorlist2[] = '#000066'; break;	// blue
		case 'Not Scanned' : $colorlist2[] = '#c2c2a3'; break;		// grey
		case 'Missing: 6 or more' : $colorlist2[] = '#ff0000'; break;	// red
		case 'Missing: 3 - 5' : $colorlist2[] = '#ff6600'; break;	// orange
		case 'Missing: 1 - 2' : $colorlist2[] = '#ffff00'; break;	// yellow
		case 'Fully Patched' : $colorlist2[] = '#009900'; break;		// green
			}

  
}



echo "<div class=\"heading\">";
echo "Global Patching Status";
echo "</div>";

echo "<div id=\"PatchSvrCountsGraph\" class=\"graphL\"></div>";

echo "<div id=\"PatchWsCountsGraph\" class=\"graphR\"></div>";


?>
<script type="text/javascript">
var chartSvrPatchCounts;

$(document).ready(function () {

	Highcharts.setOptions({
global: {
useUTC: false
		},
credits: {
enabled: false
		}
	});	

if (chartSvrPatchCounts) chartSvrPatchCounts.destroy();

chartSvrPatchCounts = new Highcharts.Chart({

chart: {
renderTo: 'PatchSvrCountsGraph',
type: 'pie',
height: 150,
width: 400,	
margin: [0, 0, 0, 0],
},

tooltip: { enabled: true },

 

legend: {
enabled: true,
align: 'left',
labelFormat: '<b>{name}</b> {y}',
verticalAlign: 'middle',
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
            animation: false,
			showInLegend: true,
			allowPointSelect: false,
			dataLabels: {
                format: '{point.y}',
				enabled: true,
				distance: -25,
                style: {
                    fontWeight: 'bold',
                    color: 'white'
                }
            },
            startAngle: -90,
            endAngle: 90,
            center: ['75%', '80%'],
            size: '150%',
			colors: [<?php foreach($colorlist as $item) { echo "'$item',"; }; ?>]
        }
    },
	
	
	
	
    title: {
        text: 'Server Patch Counts',
		useHTML: true,
        align: 'center',
        verticalAlign: 'middle',
		y: 60,
		x: 80,
		style: {
             fontWeight: 'bold',
            color: 'black',
        fontFamily: 'Arial,Helvetica,sans-serif',
    fontSize: '14px',
	}
 
    },	
	

series: [{
name: '',
  innerSize: '40%',
data: [<?php echo join($datax, ',') ?>],

		}]
	})
});
</script>
<?php 




?>
<script type="text/javascript">
var chartWsPatchCounts;

$(document).ready(function () {

	Highcharts.setOptions({
global: {
useUTC: false
		},
credits: {
enabled: false
		}
	});	

if (chartWsPatchCounts) chartWsPatchCounts.destroy();

chartWsPatchCounts = new Highcharts.Chart({

chart: {
renderTo: 'PatchWsCountsGraph',
type: 'pie',
height: 150,
width: 400,	
margin: [0, 0, 0, 0],
},

tooltip: { enabled: true },

 

legend: {
enabled: true,
align: 'left',
labelFormat: '<b>{name}</b> {y}',
verticalAlign: 'middle',
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
            animation: false,
			showInLegend: true,
			allowPointSelect: false,
			dataLabels: {
                format: '{point.y}',
				enabled: true,
				distance: -25,
                style: {
                    fontWeight: 'bold',
                    color: 'white'
                }
            },
            startAngle: -90,
            endAngle: 90,
            center: ['75%', '80%'],
            size: '150%',
			colors: [<?php foreach($colorlist2 as $item) { echo "'$item',"; }; ?>]
		}
    },
	
	
	
	
    title: {
        text: 'Workstation Patch Counts',
		useHTML: true,
        align: 'center',
        verticalAlign: 'middle',
		y: 60,
		x: 80,
		style: {
             fontWeight: 'bold',
            color: 'black',
        fontFamily: 'Arial,Helvetica,sans-serif',
    fontSize: '14px',
	}
 
    },	
	

series: [{
name: '',
  innerSize: '40%',
data: [<?php echo join($datay, ',') ?>],

		}]
	})
});
</script>
<?php 

//end chart


echo "<div class=\"datatable\">";
echo "<table id=\"scores\">";
echo "<tr><th class=\"colL\">Type</th><th class=\"colM\">Patch Score</th></tr>";


$row4 = sqlsrv_fetch_array( $stmt4, SQLSRV_FETCH_ASSOC);
echo "<tr><td>Server</td><td>" . round (100 - (100 * ($row4['mp'] / $row4['tp']) ),2) . "%</td></tr>";

$row5 = sqlsrv_fetch_array( $stmt5, SQLSRV_FETCH_ASSOC);
echo "<td>Workstation</td><td>" . round (100 - (100 * ($row5['mp'] / $row5['tp']) ),2) . "%</td></tr>";

echo "</table>";
echo "</div>";






$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}


echo "<div class=\"heading heading2\">";
echo "Patching Status (Patch Policy Members)";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";


echo "<div class=\"datatable\">";
echo "<table id=\"patchlist\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colL\">Patch Policies</th><th class=\"colL\">Missing</th><th class=\"colL\">Pending</th><th class=\"colL\">Failed</th><th class=\"colL\">Reboot Pending</th><th class=\"colL\">Last Checked</th><th class=\"colL\">Last Result</th></tr>";


while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
  echo "<tr><td class=\"colL\" ref=\"{$row['machName']}\">";
  showAgentIcon($row['online'],$row['currentLogin']); 
  echo "&nbsp;".$row['machName']."</td>";
  echo "<td class=\"colL\">".$row['PolicyName']."</td>";
  echo "<td class=\"colM\">".$row['missingApproved']."</td>";
  echo "<td class=\"colM\">".$row['pending']."</td>";

  echo "<td class=\"colM\">";
  if ($row['failed']>0){
    echo "<font color=\"red\">".$row['failed']."</font></td>";
  }
  else {
    echo $row['failed']."</td>";
  }

  
  echo "<td class=\"colM\">";
  if ($row['rebootPending']=="Yes") {
    echo "<font color=\"red\">".$row['rebootPending']."</font></td>";
  }
  else {
    echo $row['rebootPending']."</td>";
  }
  
  $dispdate = (isset($row['lastPatchScan']) ? date($datestyle." ".$timestyle,$row['lastPatchScan']->getTimestamp()) : 'Never');
  echo "<td class=\"colM\">".$dispdate."</td>";
  
  echo "<td class=\"colM\">";
  
  $pre="";
  $post="";
  
  if ($row['testStatusDescription']=="Untested") {
    $pre="<font color=\"orange\">";
	$post="</font>";
  }
  if ($row['testStatusDescription']=="Pending") {
    $pre="<i>";
	$post="</i>";
  }
  
  echo $pre.substr($row['testStatusDescription'],0,15);
  if (strlen($row['testStatusDescription'])>=15) { echo '...'; }
  echo $post."</td></tr>";
}
echo "</table>";
echo "</div>";




if ($NPP !== true) { 



//* spacer *//
echo "<div class=\"spacer\">";
echo "</div>";


//* non-patch policy members *//

$tsql = "SELECT distinct top ".$resultcount." vl.displayName as machName,PolicyName,missingApproved,pending,failed,
  rebootPending = case
    when rebootPending=0 then 'No'
    else 'Yes'
  end,
  lastPatchScan,testStatusDescription, vl.online as online, vl.currentLogin
  FROM vPatchPolicyMember";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = vPatchPolicyMember.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on vPatchPolicyMember.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql.=" join vAgentLabel vl on vl.agentGuid = vPatchPolicyMember.agentGuid
  join vPatchStatusByAgent pp on pp.agentGuid = vPatchPolicyMember.agentGuid
  where PolicyName = '-No policy-' and (vPatchPolicyMember.OperatingSystem like 'Windows%') and lastPatchScan is not null
  order by rebootPending desc, failed desc, missingApproved desc, pending desc";

   
  
$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

echo "<div class=\"heading heading2\">";
echo "Patching Status (No Patch Policy)";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";

echo "<div class=\"datatable\">";
echo "<table id=\"patchlist2\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colL\">Patch Policy</th><th class=\"colL\">Missing</th><th class=\"colL\">Pending</th><th class=\"colL\">Failed</th><th class=\"colL\">Reboot Pending</th><th class=\"colL\">Last Checked</th><th class=\"colL\">Last Result</th></tr>";


while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
  echo "<tr><td class=\"colL\">";
  showAgentIcon($row['online'],$row['currentLogin']); 
  echo "&nbsp;".$row['machName']."</td>";
  echo "<td class=\"colL\">".$row['PolicyName']."</td>";
  echo "<td class=\"colM\">".$row['missingApproved']."</td>";
  echo "<td class=\"colM\">".$row['pending']."</td>";

  echo "<td class=\"colM\">";
  if ($row['failed']>0){
    echo "<font color=\"red\">".$row['failed']."</font></td>";
  }
  else {
    echo $row['failed']."</td>";
  }

  
  echo "<td class=\"colM\">";
  if ($row['rebootPending']=="Yes") {
    echo "<font color=\"red\">".$row['rebootPending']."</font></td>";
  }
  else {
    echo $row['rebootPending']."</td>";
  }
  
  $dispdate = (isset($row['lastPatchScan']) ? date($datestyle." ".$timestyle,$row['lastPatchScan']->getTimestamp()) : 'Never');
  echo "<td class=\"colM\">".$dispdate."</td>";
  
  echo "<td class=\"colM\">";
  
  $pre="";
  $post="";
  
  if ($row['testStatusDescription']=="Untested") {
    $pre="<font color=\"orange\">";
	$post="</font>";
  }
  if ($row['testStatusDescription']=="Pending") {
    $pre="<i>";
	$post="</i>";
  }
  
  echo $pre.$row['testStatusDescription'].$post."</td></tr>";
}

echo "</table>";
echo "</div>";

}


sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>