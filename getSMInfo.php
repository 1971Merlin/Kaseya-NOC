<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';


  
$tsql = "SELECT compliantMachines, vulnerableMachines from SM.vNumberCompliant, SM.vNumberVulnerable";
$tsql2 = "Select * from SM.vTopXMachinesMissingPatches";
$tsql3 = "Select * from SM.vTotalVulnerabilities";
$tsql4 = "Select * from SM.vTopXVulnerabilities";
  
  
$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
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


$datax = array();

while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
  $fooa = $row['compliantMachines'];
  $foob = $row['vulnerableMachines'];
  
  $datax[] = "['Vulnerable',".$foob."]";
  $datax[] = "['Compliant',".$fooa."]";

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

echo "<div class=\"graphL\">";

echo "<div class=\"minibox\">";
	echo "<div class=\"miniheading\"># Vulnerabilities</div>";
	echo "<div class=\"mininum\">";
	echo "<font color=\"#990000\">".$numVulns."</font>";
	echo "</div>";
echo "</div>";


echo "<div id=\"smStatusGraph\" class=\"graph\"></div>";


echo "</div>";


echo "<div id=\"smTopVulnsGraph\" class=\"graphR\"></div>";

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
width: 330,	
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
			colors: ['#ff0000','#009900']
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
  echo "<tr><td class=\"colL\">".$row2['displayName']."</td>";
  echo "<td class=\"colM\">".$row2['countOfVulnerabilties']."</td></tr>";
}


echo "</table>";
echo "</div>";






sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>