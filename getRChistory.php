<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';
echo "<div class=\"heading\">Remote Control History Last 24Hrs</div>";
  


 // Classic RC // 
$tsql = "select adminname, (SUM(duration) / (1000*60)) as total, count(adminName) as num from rcLog 
		 where eventTime > DATEADD(day,-1,getutcdate()) and duration>0 and rclog.agentGuid != 123456789
		 group by adminname";


 // VSA 7.0 only //		 
$tsql2 = "select count (adminName) as count, adminName, (
	select top 1 eventTime
	from adminLog
	where eventTime > DATEADD(day,-1,getutcdate()) and description like 'Remote control%' and adminName like an.adminName
	order by eventTime desc
  ) eventTime
  from adminLog as an		 
  where an.eventTime > DATEADD(day,-1,getutcdate()) and description like 'Remote control%'
  group by adminName";

  
 // VSA R8+ //
 $tsql3 = "select adminname, sum(datediff(mi,startTime,lastActiveTime)) as total, count(adminName) as num
   from KaseyaRemoteControl.Log
   where startTime > DATEADD(day,-1,getdate()) and datediff(mi,startTime,lastActiveTime) > 0
   group by adminname";

//* table - version 7.0 only *// 

if ($KVer == 7.0 ) {

  $stmt2 = sqlsrv_query( $conn, $tsql2);
  if( $stmt2 === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }

  echo "<div class=\"datatable\">";
  echo "<table id=\"RClist\"><p>KRC Sessions</p>";
  echo "<tr><th class=\"colL\">Admin Name</th><th class=\"colL\">Sessions</th><th class=\"colL\">Most Recent</th></tr>";

  while( $row = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC))
  {
	echo "<tr><td class=\"colL\">".$row['adminName']."</td>";
	echo "<td class=\"colM\">".$row['count']."</td>";
	echo "<td class=\"colL\">".$row['eventTime']->format($datestyle." ".$timestyle)."</td>";
	echo "</tr>";
  }
  echo "</table>";
  echo "</div>";
}


//* the graph - classic, R8+*//

$datax = array();


// get classic sessions info //

$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
 if ($row['total']>0) { $datax[] = "{name : '".$row['adminname']."', user : '".$row['adminname']."', y : ".$row['total'].", cl : ".$row['num']."}"; }
}

// gert R8 sessions info //

if ($KVer > 7.0 ) {

  $stmt3 = sqlsrv_query( $conn, $tsql3);
  if( $stmt3 === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }

  while( $row = sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC))
  {
   if ($row['total']>0) { $datax[] = "{name : '".$row['adminname']."', user : '".$row['adminname']."', y : ".$row['total'].", krc : ".$row['num']."}"; }
  }
}



//* If no data, no graph! *//
if ($datax == null) { return; }


echo "<div id=\"rcgraph\" class=\"graph\"></div>";
?>
<script  type="text/javascript">
var chartRC;

$(function () {
Highcharts.setOptions({
	global: {
		useUTC: false
	},
	credits: {
		enabled: false
	}
});

if (chartRC) chartRC.destroy();

chartRC = new Highcharts.Chart({

        chart: {
			renderTo: 'rcgraph',
            type: 'pie',
            options3d: {
				enabled: true,
                alpha: 45,
                beta: 0,
            },
            height: 180,
			width: 265,	
			margin: [0, 0, 0, 60],
        },
		
		tooltip: { enabled: false },
		
		legend: {
			align: 'left',
            verticalAlign: 'top',
			layout: 'vertical',
			labelFormat: '{name} ({y} mins)',
			symbolHeight: 9,
		    itemStyle: { fontSize : '9px' },
			margin: 0,
            borderWidth: 1,
			borderRadius: 3,
			backgroundColor: '#f0f0f0'
        },

		plotOptions: {
			pie: {
				animation: false,
				depth: 25,
				dataLabels: {
                    enabled: true,
					distance: -20,
					color: 'red',
					backgroundColor: 'rgba(252, 255, 197, 0.7)',
					borderColor: 'gray',
					borderWidth: 1,
					borderRadius: 3,
                    format: '{point.name}<br/>{point.y} mins',
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
			name: 'RC Time',
			data: [<?php echo join($datax, ',') ?>],
		}]
	})
});
</script>
<?php
sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>