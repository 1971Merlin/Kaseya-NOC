<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

$left=true;
$right=true;

$id=$_GET["type"];
if ($id=='l') { $right=false; }
if ($id=='r') { $left=false; }


$tsql = "select top ".$resultcount." Machine from (
  SELECT distinct Machine_GroupID as Machine
  FROM vMachine";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = vMachine.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on vMachine.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql.=" Where vMachine.groupName like 'root%'

  union
  
  SELECT distinct Machine_GroupID as Machine
  FROM vMachine
  Where vMachine.groupName like 'root.unnamed'
)  foo1";


$tsql2 = "select sum(num) as num from (
  SELECT COUNT(distinct vMachine.agentGuid) as num
  FROM vMachine";
if ($usescopefilter==true) { $tsql2.=" join vdb_Scopes_Machines foo on (foo.agentGuid = vMachine.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql2.=" 
 join dbo.DenormalizedOrgToMach on vMachine.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
 $tsql2.=" Where vMachine.groupName like 'root%'

  union all SELECT count(distinct vMachine.agentGuid) as num
  FROM vMachine
  Where vMachine.groupName like 'root.unnamed'

) foo2";


$stmt2 = sqlsrv_query( $conn, $tsql2);
if( $stmt2 === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}
  $row_count = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC);

if ($left==true) {

echo "<table class=\"datatable\"><tr><td>";

if ($row_count['num']==0) {
	 echo "<img src=\"images/check.png\">";
}
 else {
	 echo "<img src=\"images/question.png\">";
 }
 echo "</td><td>{$row_count['num']} Ungrouped Agent";
 if ($row_count['num'] != 1) { echo 's'; }
 echo "</td></tr></table>";

 }



if ($right==true) {

echo "<div class=\"heading\">";
echo "Ungrouped Agents";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";

if ($row_count['num']!=0) {

  $stmt = sqlsrv_query( $conn, $tsql);
  if( $stmt === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }

  echo  "<table id=\"nogrouplist\" class=\"datatable\">";
  echo  "<tr><th class=\"colL\">Machine Name</th></tr>";

  while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
  {
     echo  "<tr><td class=\"colL\">".$row['Machine']."</td></tr>";
  }
  echo  "</table>";
}

}
sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>