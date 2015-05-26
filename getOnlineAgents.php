<?php 
$pageContent = null;
ob_start();
  include 'dblogin.php';
  echo "<div class=\"heading\">Agents Online Last 24Hrs</div>";

  $tsql = "SELECT eventTime, value
  FROM vsaFloatStats where queryId=3 and eventTime >= DATEADD(day, -1, GETDATE())
  order by eventTime";

$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}
 
$datax = array();

while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
   $datax[] = "[".$row['eventTime']->getTimestamp()."000,".$row['value']."]";
}

if ( count($datax)>1 ) { 

echo "<div id=\"agentgraph\" class=\"graph\"></div>";

?>
<script type="text/javascript">
var chartOL;

$(document).ready(function () {
Highcharts.setOptions({
	global: {
		useUTC: false
	},
	credits: {
		enabled: false
	}
});

	if (chartOL) chartOL.destroy();

	chartOL = new Highcharts.Chart({

    chart: {
		renderTo: 'agentgraph',
        height: 180,
		width: 350,
        type: 'line',
		plotBorderWidth: 1,
		spacingLeft: 0, 
		spacingBottom: 5,
			
    },

	xAxis: {
	    type: 'datetime',
		tickPixelInterval: 35,
		tickLength: 6,
		tickPosition: 'outside',
		tickWidth: 1,
		minorGridLineWidth: 0,
		minorTickInterval: 'auto',
		minorTickPosition: 'inside',
		minorTickLength: 3,
        minorTickWidth: 1,
        gridLineWidth: 1,
		dateTimeLabelFormats : {
                  hour: '%H:%M',
				  day: '%H:%M'
        },
		labels: {
			style: {
				fontSize: '8px'
			},
		}
	},
	
	plotOptions: {
		series: {
			animation: false,
			marker: {
				enabled: false
			},
		},			
        enableMouseTracking: false
	},

	yAxis: {
		tickPixelInterval: 25,
		minorGridLineDashStyle: 'shortdash',
		minorTickInterval: 5,
		min: 0,
		title: {
			enabled: true,
			text: 'Online Agents',
			style: {
				fontSize: '8px'
			},
		},
	},
		
	title: {
		text: null
	},
		
	tooltip: {
		formatter: function() {
			return this.series.name + ' : <b>'+ this.y +'</b>' + ' at ' + Highcharts.dateFormat('%H:%M',new Date(this.x));
		}
	},

	series: [{
		showInLegend : false,
		name : 'Agents Online',
		data: [<?php echo join(",", $datax) ?>]
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