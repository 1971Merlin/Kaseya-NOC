<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

$left=true;
$right=true;

$id=$_GET["type"];
if ($id=='l') { $right=false; }
if ($id=='r') { $left=false; }


$tsql = "select agentVersion from siteparams";
 
$tsql2 = "select top 1 agentVersion from users
 join userIpInfo ip on ip.agentGuid = users.agentGuid
 where lower(ostype) <> 'mac os x' and lower(ostype) != 'linux'
 order by agentVersion desc";


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


  $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC);
  $topver=$row['agentVersion'];

  $row = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);
  $realver=$row['agentVersion'];

  
  if ($realver>$topver) { $val = $realver; }
	else 
  { $val=$topver; }
  
$tsql3 = "Select distinct top ".$resultcount." vl.machine_GroupID as machName, agentVersion from users";
if ($usescopefilter==true) { $tsql3.=" join vdb_Scopes_Machines foo on (foo.agentGuid = users.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql3.=" 
 join dbo.DenormalizedOrgToMach on users.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql3.=" join vAgentLabel vl on vl.agentGuid = users.agentGuid
  join userIpInfo ip on ip.agentGuid = users.agentGuid
  where agentVersion<'".$val."' and lower(ostype) <> 'mac os x' and lower(ostype) != 'linux'";


$tsql4 = "Select count(distinct users.agentGuid) as tally from users";
if ($usescopefilter==true) { $tsql4.=" join vdb_Scopes_Machines foo on (foo.agentGuid = users.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql4.=" 
 join dbo.DenormalizedOrgToMach on users.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql4.=" join userIpInfo ip on ip.agentGuid = users.agentGuid
  where agentVersion < '".$val."' and lower(ostype) <> 'mac os x' and lower(ostype) != 'linux'";




$stmt4 = sqlsrv_query( $conn, $tsql4);
if( $stmt4 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

  $row = sqlsrv_fetch_array( $stmt4, SQLSRV_FETCH_ASSOC);
  $tally = $row['tally'];


if ($left==true) {


echo "<table class=\"datatable\"><tr><td>";

if ($tally==0) {
	 echo "<img src=\"images/check.png\">";
}
 else {
	 echo "<img src=\"images/question.png\">";
 }
 echo "</td><td>{$tally} Outdated Agent";
 if ($tally != 1) { echo 's'; }
 echo "</td></tr></table>";

 }



if ($right==true) {

echo "<div class=\"heading\">";
echo "Outdated Agents";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";

if ($tally!=0) {

  $stmt3 = sqlsrv_query( $conn, $tsql3);
  if( $stmt3 === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }
  
  
  echo '<p>Server current Windows agent is '.fmtver($topver)."</br>";
  echo 'Highest Windows agent present is '.fmtver($realver)."</p>";
  echo "<table id=\"oldagents\" class=\"datatable\">";
  echo "<tr><th class=\"colL\">Agent Name</th><th class=\"colM\">Version</th></tr>";

  while( $row = sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC))
  {
     echo "<tr><td class=\"colL\">".$row['machName']."</td>";
	 echo "<td class=\"colM\">".fmtver($row['agentVersion'])."</td>";
	 echo "</td></tr>";
  }
  echo "</table>";
}

}

sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;


function fmtver($ver)
{
   $pver = floor($ver/1000000);
   $sver = floor(($ver-($pver*1000000))/10000);
   $tver = floor(($ver-($pver*1000000)-($sver*10000))/100);
   $bld = $ver-($pver*1000000)-($sver*10000)-($tver*100);
   return $pver.'.'.$sver.'.'.$tver.'.'.$bld;
}
?>