<?php
$pageContent = null;
ob_start();
include 'dblogin.php';


$tsql = " SELECT top ".$resultcount." lower(summary) as tsummary, creation_datetime as created,
 lower(LEFT(submitter_name,30)) as submitter_name,  lower(LEFT(assignee,30)) as tassignee, 
 lower(LEFT(stage,30)) as Stage, org_ref as Org, REF , serv_desk
 FROM kasadmin.vbo_SDIncidents_List where status != 'Closed' and stage != 'Solved' and stage NOT like 'Closed'
 
 ORDER BY Ref Desc ";
 

$tsql2 = "SELECT count(summary) as num FROM kasadmin.vbo_SDIncidents_List where status NOT like 'Closed' and stage NOT like 'Solved' and stage NOT like 'Closed' ";
  


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

// Fetch total tickets
$row_count = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);
$ticket_count = $row_count['num'];

echo "<div class=\"heading\">";
echo "Service Desk Tickets - {$ticket_count}";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";

echo "<div class=\"datatable\">";
echo "<table id=\"sdlist\">";

echo '<tr><th class="colL">Desk</th><th class="colL">Ref</th><th class="colL">Summary</th><th class="colL">Creator</th><th class="colL">Assignee</th><th class="colL">Stage</th><th class="colL">Orginization</th><th class="colL">Age</th></tr>';


while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
	$row['Org'] = ( empty($row['Org']) ) ? '<span style="font-weight: bold; color: #FF0000">none</span>' : $row['Org'];	

	$row['tassignee'] = ( empty($row['tassignee']) ) ? 'none' : $row['tassignee'];		 
 
	echo "<tr>";
	
	echo "<td class=\"colL\">".$row['serv_desk']."</td>";

	echo "<td class=\"colL\">".$row['REF']."</td>";
	
	
	echo "<td class=\"colL\">".htmlspecialchars($row['tsummary'])."</td>";

	echo "<td class=\"colL\">";
     if ($row['Stage']=='new ticket') {
      echo "<font color=red>".$row['submitter_name']."</font></td>";
    } else {
      echo $row['submitter_name']."</td>";
    }

	

	echo "<td class=\"colL\">";
     if ($row['Stage']=='new ticket') {
      echo "<font color=red>".$row['tassignee']."</font></td>";
    } else {
      echo $row['tassignee']."</td>";
    }	


echo '<td class="colL">';
switch ( $row['Stage'] )
{
	case 'new ticket':
		echo '<span style="color: #FF0000">' . $row['Stage'] . '</span>';
		break;
	
	case 'on hold':
		echo '<span style="color: #FFCC00">' . $row['Stage'] . '</span>';
		break;
	
	default:
		echo $row['Stage'];
		break;

}
echo '</td>';



	

    echo "<td class=\"colL\">";
     if ($row['Stage']=='new ticket') {
      echo "<font color=red>".$row['Org']."</font></td>";
    } else {
      echo $row['Org']."</td>";
    }	 
	 
	
 //    echo "<td class=\"colL\">".substr($row['Message'],0,200)."</td></tr>";
 
	// Age
	echo '<td class="colL">' . formatDateDiff($row['created'],new datetime("now")) . '</td>';
 
 
	echo '</tr>';
	
}

echo "</table>";
echo "</div>";

sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>