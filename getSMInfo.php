<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';



// dont try to simplify tsql1. Needs to be this way as now rows are returned if no machines are counted (not null or zero returned!!)
  
$tsql = "SELECT * from 
(select isnull(min(compliantMachines),0) as compliantMachines from SM.vNumberCompliant) t1,
(select isnull(min(vulnerableMachines),0) as vulnerableMachines from SM.vNumberVulnerable) t2,
(select count(AgentGuid) as fullyPatchedMachines from SM.vFullyPatchedAgents) t3
";

$tsql2 = "Select top 10 vl.displayName, countOfVulnerabilties, vl.online as online, vl.currentLogin
 from SM.vTopXMachinesMissingPatches
 join vAgentLabel vl on vl.agentGuid = SM.vTopXMachinesMissingPatches.agentGuid
 order by countOfVulnerabilties desc";

$tsql3 = "Select count(machName) as totalVulnerabilities from SM.vUnappliedPatches where ApprovalStatus not like 'Suppressed'";
$tsql4 = "Select top 5 Name, countOfMachines from SM.vTopXVulnerabilities order by countOfMachines desc";
$tsql5 = "Select count(agentguid) as machines from SM.machine";
 
 $tsql6 = "SELECT top ".$resultcount." vl.displayName,DateCreated,OperationCategory,RequestJson,JobName,AgentJobScheduleTime,TotalToComplete,NumberComplete,PercentComplete,DateModified,ProgressBarValue, vl.online as online, vl.currentLogin
  FROM SM.OperationQueue
  join vAgentLabel vl on vl.agentGuid = sm.OperationQueue.agentGuid
  where vl.online > 0
  order by DateModified desc, OperationCategory, displayName";


$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
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

$stmt2 = sqlsrv_query( $conn, $tsql2);
if( $stmt2 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}
  
echo "<div class=\"heading\">";
echo "Software Management Status";
echo "</div>";

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

$stmt6 = sqlsrv_query( $conn, $tsql6);
if( $stmt6 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}


$datax = array();

while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
  $fooa = $row['compliantMachines'];
  $foob = $row['vulnerableMachines'];
  $fooc = $row['fullyPatchedMachines'];
  
  $datax[] = "['Vulnerable',".$foob."]";
  $datax[] = "['Compliant',".$fooa."]";
  $datax[] = "['Fully Patched',".$fooc."]";
}



$datay = array();
$dataz = array();

while( $row4 = sqlsrv_fetch_array( $stmt4, SQLSRV_FETCH_ASSOC))
{
  $fooa = $row4['Name'];
  $foob = $row4['countOfMachines'];
  
  $datay[] = "['".$fooa."']";
  $dataz[] = $foob;
}



$row3 = sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC);
$numVulns = $row3['totalVulnerabilities'];

$row5 = sqlsrv_fetch_array( $stmt5, SQLSRV_FETCH_ASSOC);
$numMachines = $row5['machines'];



echo "<div class=\"graphL\">";

	echo "<div class=\"minibox\">";
		echo "<div class=\"miniheading\"># Vulnerabilities</div>";
		echo "<div class=\"mininum\">";
		echo "<font color=\"#990000\">".$numVulns."</font>";
		echo "</div>";
	echo "</div>";

	echo "<div class=\"minibox\">";
		echo "<div class=\"miniheading\"># Managed Machines</div>";
		echo "<div class=\"mininum\">";
		echo "<font color=\"#990000\">".$numMachines."</font>";
		echo "</div>";
	echo "</div>";





	if (empty($datax)==false) { echo "<div id=\"smStatusGraph\" class=\"graph\"></div>"; }

echo "</div>";



if (empty($datay)==false) { echo "<div id=\"smTopVulnsGraph\" class=\"graphR\"></div>"; }


// spacer
echo "<div class=\"spacer\"></div>";


?>
<script type="text/javascript">
var chartSMCounts;

$(document).ready(function () {

	Highcharts.setOptions({
global: {
useUTC: false
		},
credits: {
enabled: false
		}
	});	

if (chartSMCounts) chartSMCounts.destroy();

chartSMCounts = new Highcharts.Chart({

chart: {
renderTo: 'smStatusGraph',
type: 'pie',
height: 150,
width: 350,	
margin: [0, 0, 0, 0],
animation: false,
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
margin: 1,
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
            center: ['65%', '80%'],
            size: '150%',
			colors: ['#ff0000','#009900','#1248ce']
        }
    },
	
	
	
	
    title: {
        text: 'Agent Vulnerablity Status',
		useHTML: true,
        align: 'center',
        verticalAlign: 'middle',
		y: 60,
		x: 40,
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
//end chart





?>
<script type="text/javascript">
var chartSMTopVuln;

$(document).ready(function () {

Highcharts.setOptions({
global: {
useUTC: false
		},
credits: {
enabled: false
		}
	});	

if (chartSMTopVuln) chartSMTopVuln.destroy();

chartSMTopVuln = new Highcharts.Chart({

chart: {
	renderTo: 'smTopVulnsGraph',
	type: 'column',
	height: 250,
	width: 300,	
    borderColor: '#eeeeee',
    borderWidth: 1,
	animation: false,
},

tooltip: { enabled: true },



plotOptions: {
        series: {
			pointPadding: 0.1,
            groupPadding: 0,
			borderWidth: 0
        }
    },
	

    legend: {
        enabled: false
    },

	
	
    title: {
        text: 'Top 5 Vulnerablities',
		useHTML: true,
        align: 'center',
        verticalAlign: 'top',
		style: {
             fontWeight: 'bold',
            color: 'black',
			fontFamily: 'Arial,Helvetica,sans-serif',
			fontSize: '14px',
		}
 
    },	
	

xAxis : { categories: [<?php echo join($datay, ',') ?>],

		crosshair: true,

},


yAxis: {
        min: 0,
		max: <?php echo max($dataz) ?>,
        title: {
            text: 'Number of'
        }
    },


series: [{
	
	name: 'Count',
	
data: [<?php echo join($dataz, ',') ?>],

		}]
	})
});
</script>
<?php 
//end chart


echo "<div class=\"heading heading2\">";
echo "Top 10 Vulnerable Machines";
echo "</div>";



echo "<div class=\"datatable\">";
echo "<table id=\"TopXMachinesMissingPatches\">";
echo "<tr><th class=\"colL\">Machine</th><th class=\"colL\"># of Vulnerabilities</th></tr>";


while( $row2 = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC))
{
  echo "<tr><td class=\"colL\">";
  showAgentIcon($row2['online'],$row2['currentLogin']); 
  echo "&nbsp;".$row2['displayName']."</td>";
  echo "<td class=\"colM\">".$row2['countOfVulnerabilties']."</td></tr>";
}


echo "</table>";
echo "</div>";


echo "<div class=\"heading heading2\">";
echo "Machines with Current Activity";
echo "<div class=\"topn\">showing top ".$resultcount."</div>";
echo "</div>";


echo "<div class=\"datatable\">";
echo "<table id=\"ActiveMachines\">";
echo "<tr><th class=\"colL\">Machine</th><th class=\"colL\">Activity</th><th class=\"colM\">Progress %</th></tr>";


while( $row6 = sqlsrv_fetch_array( $stmt6, SQLSRV_FETCH_ASSOC))
{
	
  echo "<tr><td class=\"colL\">";
  showAgentIcon($row6['online'],$row6['currentLogin']); 
  echo "&nbsp;".$row6['displayName']."</td>";
  echo "<td class=\"colL\">".$row6['OperationCategory']."</td>";
  
  
  
  
  
  
  echo "<td class=\"colM\" style=\"background-image: url('images/1x1blue.png'); background-repeat: no-repeat; background-size:".$row6['ProgressBarValue']."% 100%;\">".$row6['ProgressBarValue']."</td>";
  echo "</tr>";

}

// Blackout 	-> Pause symbol
// Scheduled 	->  Green 'scan now' symbol
// PD_Deploy 	-> CD/Floppy green down arrow icon
// Progress 	-> CD/Floppy green down arrow icon
// Scan 		-> Green 'scan now' symbol
// PD_Res 		-> Resuming 		-> CD/Floppy green down arrow icon
// PD_Rescan	-> Rescanning files -> CD/Floppy green down arrow icon
// PD_RebReb	-> Rebooting	-> Power button icon


echo "</table>";
echo "</div>";




sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>