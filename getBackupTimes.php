<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

echo "<div class=\"heading\">";
echo "<image src=\"images/acronis-logo.png\" style=\"vertical-align:middle\"> Kaseya Backup Activities Duration (Last 24Hrs)";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";


$tsql = "select distinct top ".$resultcount." vb.Machine_GroupID as machName, EventTime, description, result, imageSize, durationSec, st.online as online, st.currentLogin
 from vBackupLog as vb";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = vb.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on vb.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql.=" join vAgentLabel st on st.agentGuid = vb.agentGuid 
 where EventTime > DATEADD(day,-1,getdate()) and ISNULL(durationSec,0) > 0 
 order by machName, EventTime";

$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$datax = array();

while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
  $datax[] = $row;
}


if ( count($datax)>0 ) { 
	

$hght=count($datax)*15+160;

echo "<div id=\"backupgraph\" class=\"graph\" style=\"width: 800px; height: {$hght}px\"></div>";
?>
<script type="text/javascript">
var chartBU;

$(document).ready(function () {
Highcharts.setOptions({
	global: {
		useUTC: false
	},
	credits: {
		enabled: false
	}
});

	if (chartBU) chartBU.destroy();

	chartBU = new Highcharts.Chart({

        chart: {
			renderTo: 'backupgraph',
			width: 800,
			height: <?php echo count($datax)*15+160?>,
 	        type: 'columnrange',
			plotBorderWidth: 1,
			inverted: true,
        },

		plotOptions: {
			columnrange: {
				animation: false,
				pointPadding: 0,
				groupPadding: 0,
				pointWidth: 10,
			},			
            enableMouseTracking: false,
			area: {
				events: {
					legendItemClick: function () {
						return false; // <== returning false will cancel the default action
					}
				}
			},
		},
		

		
		yAxis: {
			type: 'datetime',
			alternateGridColor: '#FDFFD5',
			max: new Date().getTime(),
			title : {
				text: null,
			},
			labels: {
				rotation: -45,
			},
			dateTimeLabelFormats: {
                  hour: '%l:%M %P',
				  day: '%A',
			},
			tickInterval: 3600000,
			minorGridLineDashStyle: 'dash',
			minorTickInterval: 900000,	
			tickmarkPlacement: 'on',
			tickLength: 6,
			tickPosition: 'outside',
			tickWidth: 1,				
		}, 

		title: {
			text: null
		},
	
		tooltip : {
			formatter: function() {
				return 'Backup Activity at ' + Highcharts.dateFormat('%H:%M',new Date(this.point.low)) + " until " + Highcharts.dateFormat('%H:%M',new Date(this.point.high));
			}
		},
		
		xAxis: {
			tickmarkPlacement: 'on',
			tickLength: 6,
			tickPosition: 'outside',
			tickWidth: 1,	
			title : {
				text: 'Agent Name',
				style: {
				  "fontWeight" : "Bold",
				  "font" : "12px", 
				  "color" : "black",
				},
				
			},
			gridLineWidth : 1,
			categories: [<?php		
$firstin=true;
  
foreach ($datax as $item) {

	if ($firstin==false) { echo ", "; };
	$firstin = false;

	echo "'".$item['machName']."'";
}
  echo "]\n";	
  echo "},\n";
  echo "\n";

  echo "series: [{\n";
  echo "name: 'Backup Activities Duration',\n";
  echo "color: '#c00000',\n";
  echo "data: [\n";
  
  $firstin=true;
  
foreach ($datax as $item) {

  if ($firstin==false) { echo ",\n"; };
  $firstin = false;

  echo "[";  
  $length=$item['EventTime']->getTimestamp()-$item['durationSec'];
  echo $length."000";
  echo ",";
  echo $item['EventTime']->getTimestamp()."000";
  echo "]";
}  
?>]
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