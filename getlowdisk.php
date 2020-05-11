<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';


$tsql = "select distinct top ".$resultcount." st.displayName as MachineName, DriveLetter, TotalSpace, UsedSpace, FreeSpace,
 (cast(freespace as float)/cast(totalspace as float))*100 as percFree,
 rank = case
   when FreeSpace <= 1024*10 and (cast(freespace as float)/cast(totalspace as float))*100 <= 10 then 1
   when FreeSpace <= 1024*10 or (cast(freespace as float)/cast(totalspace as float))*100 <= 10 then 2
   when FreeSpace <= 1024*15 and (cast(freespace as float)/cast(totalspace as float))*100 <= 15 then 3
   when FreeSpace <= 1024*15 or (cast(freespace as float)/cast(totalspace as float))*100 <= 15 then 4
   else 5
 end, st.online as online, st.currentLogin, VolumeName
 from vCurrDiskInfo";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = vCurrdiskInfo.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on vCurrDiskInfo.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql.=" join vAgentLabel st on st.agentGuid = vCurrdiskInfo.agentGuid
 where DriveType = 'Fixed' and TotalSpace >0 and VolumeName not like '%recovery%' and VolumeName not like 'System Reserved' and VolumeName not like 'HP_TOOLS'
 and case
   when FreeSpace <= 1024*10 and (cast(freespace as float)/cast(totalspace as float))*100 <= 10 then 1
   when FreeSpace <= 1024*10 or (cast(freespace as float)/cast(totalspace as float))*100 <= 10 then 2
   when FreeSpace <= 1024*15 and (cast(freespace as float)/cast(totalspace as float))*100 <= 15 then 3
   when FreeSpace <= 1024*15 or (cast(freespace as float)/cast(totalspace as float))*100 <= 15 then 4
   else 5
  end < 5
  order by rank, FreeSpace, machineName";


$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

echo "<div class=\"heading\">";
Echo "Agents with Low Disk Space";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";



$datax = array();
$datay = array();

while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{

  $datay[] = array($row['MachineName'],$row['percFree'],$row['FreeSpace']);
  

}



echo "<div id=\"lowDiskGraph\" class=\"graphL\"></div>";


// spacer
echo "<div class=\"spacer\"></div>";



?>
<script type="text/javascript">
var chartLDCounts;

$(document).ready(function () {

	Highcharts.setOptions({
global: {
useUTC: false
		},
credits: {
enabled: false
		}
	});	

if (chartLDCounts) chartLDCounts.destroy();

chartLDCounts = new Highcharts.Chart({

chart: {
renderTo: 'lowDiskGraph',
type: 'column',
height: 400,
width: 650,	
alignTicks: false,
animation: false,
},

tooltip: { enabled: true,
shared: true
 },

 

legend: {
enabled: false,
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
	

  
        column: {
            	animation: false,

				dataLabels: {
					enabled: true
				}
			
        }
    },
	
	
	
	
    title: {
        text: 'Agents with Low Disk Space',
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
	
	
xAxis : { categories: [<?php 


   foreach($datay as $val) {
	echo "'".$val[0]."',";
	}


?>]


},



    yAxis: [{
        title: {
            text: 'Free Space (Mb)',
			style: {
			color: 'teal'
			},
        },
		labels: {
			style: {
			color: 'teal'
			},
    formatter: function () {
      return this.value + ' Mb';
    },
	min: 0,
	max: <?php echo max(array_column($datay, 2)) ?>,
  }
    }, {
        title: {
            text: 'Free Space (Percent)',
			style: { 
				color : 'purple'
			},
        },
		labels: {
			style: {
			color: 'purple'
			},
	 formatter: function () {
      return this.value + ' %';
    },

		},

		min : 0,
		max : 15,
        opposite: true,
    }],

	

series: [<?php




$percfree = array_column($datay, 1);
$freesize = array_column($datay, 2);



	echo "{ name: '% Free', type: 'line', yAxis:1, zIndex: 2, color: 'purple', data: [";
	

    foreach($percfree as $val) {
			echo $val,", ";
		}
	
	echo "]},";


	echo "{ name: 'Mb Free',  type: 'column', zIndex: 1, color: 'teal', data: [";
	

    foreach($freesize as $val) {
			echo $val,", ";
		}
	
	echo "]}";


	
?>]
	


	
		
	})
});
</script>
<?php 
//end chart




echo "<div class=\"heading heading2\">";
echo "Agents with low Disk Space";
echo "</div>";






$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}


echo "<div class=\"datatable\">";
echo "<table id=\"lowdisklist\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colM\">Drive</th><th class=\"colL\">Volume Label</th><th class=\"colR\">Free Space</th><th class=\"colR\">Total Space</th><th class=\"colM\">Free Space %</th></tr>";


while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{


  
  echo "<tr><td class=\"colL\">";
  showAgentIcon($row['online'],$row['currentLogin']);
  echo "&nbsp;".$row['MachineName']."</td>";
  
  
  $color = "black";
  if ($row['DriveLetter'] == "C") { $color="red"; };
  
  echo "<td class=\"colM\"><font color=\"".$color."\">".$row['DriveLetter'].":</font></td>";

  echo "<td class=\"colL\">".$row['VolumeName']."</td>";
  
  echo "<td class=\"colR\">";

  if ($row['FreeSpace'] < 1024*15) {
     if ($row['FreeSpace'] < 1024*10) {
        echo "<font color=red>";
     } else {
        echo "<font color=orange>";
     }
     echo formatBytes($row['FreeSpace']*1024*1024)."</font></td>";

  } else { echo formatBytes($row['FreeSpace']*1024*1024)."</td>"; }

  echo "<td class=\"colR\">".formatBytes($row['TotalSpace']*1024*1024)."</td><td class=\"colM\">";

  $perc = round(($row['percFree']),2);

  if ($perc < 15) {
     if ($perc < 10) {
        echo "<font color=red>";
     } else {
        echo "<font color=orange>";
     }
     echo $perc."%</font></td>";

  } else { echo $perc."%</td>"; }

//  echo "<td class=\"colM\">".$row['rank']."</td></tr>";
  echo "</tr>";
}
echo "</table>";
echo "</div>";

sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>