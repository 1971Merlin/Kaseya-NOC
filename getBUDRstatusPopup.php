<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

if (isset($_GET['name'])) {

  $agent=$_GET['name'];


$tsql = "select distinct vBackupLog.Machine_GroupID, EventTime, description, result, imageSize, durationSec, st.online as online, st.currentLogin
 from vBackupLog";
 if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = vBackupLog.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on vBackupLog.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql.=" join vAgentLabel st on st.agentGuid = vBackupLog.agentGuid 
 where EventTime > DATEADD(day,-1,getdate()) and vBackupLog.Machine_GroupID like '".$agent."'
 order by EventTime DESC";
 

$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

echo "<div class=\"heading\">";
echo "Kaseya Backup Logs Last 24Hrs";
echo "</div>";

echo "<div class=\"datatable\">";
echo "<table id=\"backuplogslist\">";
echo "<tr><th class=\"colL\">Age</th><th class=\"colL\">Backup Size</th><th class=\"colL\">Elapsed Time</th><th class=\"colL\">Details</th></tr>";


while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
  echo "<tr><td class=\"colL\">".formatdatediff($row['EventTime']->format('Y/m/d H:i:s'),new datetime("now"))."</td>";
  
  
   
  echo "<td class=\"colR\">";
  if ($row['imageSize']==0) echo ''; else echo formatbytes($row['imageSize']);
  echo "</td>";
  
  
  echo "<td class=\"colL\">";
  if ($row['durationSec']==0) echo ''; else echo secondsToTime($row['durationSec']);
  echo "</td>";
  

 
  $details = $row['description'];
  $more = false;

  echo "<td class=\"colL\">";

  if (strlen($details)>100) {
    $details = substr($details,0,99);
	$more = true;
  }

	 
  if ($row['result']!=1) {
    echo "<font color=\"FF0000\">";

    echo $details;
    if ($more == true) {
      echo "...";
    } 
    echo "</td></tr></font>";
  }
  else
  {
    echo $details;
    if ($more == true) {
      echo "...";
    } 
    echo "</td></tr>";
  }
}

echo "</table>";
echo "</div>";

sqlsrv_close( $conn );
} else echo "Parameter Missing!";
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;


function secondsToTime($inputSeconds) {
	$doPlural = function($nb,$str){return $nb>1?$str.'s':$str;};

    $secondsInAMinute = 60;
    $secondsInAnHour  = 60 * $secondsInAMinute;
    $secondsInADay    = 24 * $secondsInAnHour;

    // extract days
    $days = floor($inputSeconds / $secondsInADay);

    // extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);

    // extract minutes
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);

    // extract the remaining seconds
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);

    // return the final array
    $obj = "";
    if ($days>0) $obj .= $days.$doPlural($days," day")." ";
	if ($hours>0) $obj .= $hours.$doPlural($hours," hr")." ";
	if ($minutes>0) $obj .= $minutes.$doPlural($minutes," min")." ";
	$obj .= $seconds.$doPlural($seconds," sec");
    return $obj;
}
?>