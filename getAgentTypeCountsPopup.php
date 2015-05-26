<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

if (isset($_GET['name'])) {

  $osName=$_GET['name'];
  
  if (strpos($osName,'**')>0)
  {
	$tooltip = substr($osName,0,strpos($osName,'**'));
  } else
  {

  $tooltip="";
    
  $tsql3 = "select distinct vl.displayName as machName
    from userIpInfo";
if ($usescopefilter==true) { $tsql3.=" join vdb_Scopes_Machines foo on (foo.agentGuid = userIpInfo.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql3.=" 
  join dbo.DenormalizedOrgToMach on userIpInfo.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql3.=" join vAgentLabel vl on vl.agentGuid = userIpInfo.agentGuid";
$tsql3.=" where userIpInfo.OsInfo = '".$osName."'";   

  $stmt3 = sqlsrv_query( $conn, $tsql3);
  if( $stmt3 === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }

  while ( $tp=sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC) )
  { 
	if ($tooltip!="") { $tooltip.="<br />"; }
	$tooltip.=$tp['machName'];
  }
}

  echo $tooltip;  

sqlsrv_close( $conn );
} else echo "Parameter Missing!";
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>