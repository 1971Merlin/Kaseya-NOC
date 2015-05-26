<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';


$tsql = "SELECT top ".$resultcount." policy.vPolicyAgentStatusRpt.MachineId,policy.vPolicyAgentStatusRpt.policyName,
  policy.vPolicyAgentStatusRpt.policyStatus, st.online as online, st.currentLogin
  FROM policy.vPolicyAgentStatusRpt";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = policy.vPolicyAgentStatusRpt.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on policy.vPolicyAgentStatusRpt.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql.=" join vAgentLabel st on st.agentGuid = policy.vPolicyAgentStatusRpt.agentGuid
  where policy.vPolicyAgentStatusRpt.policyStatus not like 'In Compliance'
  order by policy.vPolicyAgentStatusRpt.MachineId";

  
// table based query //
$tsql2="Select policyStatusCode as ID, count(distinct policy.VpolicyAgentStatus.agentGuid) as count
from policy.VpolicyAgentStatus";
if ($usescopefilter==true) { $tsql2.=" join vdb_Scopes_Machines foo on (foo.agentGuid = policy.VpolicyAgentStatus.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql2.=" 
 join dbo.DenormalizedOrgToMach on policy.VpolicyAgentStatus.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
$tsql2.=" where policyStatusCode in (5,10,15,20)
 group by policyStatusCode";
 
// 5 - in, 10 - out, 15 - pending, 20 - override
  
  
// agents with no policy  
$tsql3="select t1.agents-t2.withpolicy as num from
(select count(distinct agentGuid) as agents from agentState) t1,
(select count(distinct agentGuid) as withpolicy from policy.vPolicyAgentStatusRpt) t2";


  
$stmt2 = sqlsrv_query( $conn, $tsql2);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$stmt3 = sqlsrv_query( $conn, $tsql3);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

$row3 = sqlsrv_fetch_array( $stmt3, SQLSRV_FETCH_ASSOC);
$nopolicy = $row3['num'];

$datax = array();
while( $row = sqlsrv_fetch_array( $stmt2, SQLSRV_FETCH_ASSOC))
{
   $datax[$row['ID']] = $row['count'];
}

$numin = isset($datax['5'])? $datax['5'] : 0;
$numout = isset($datax['10'])? $datax['10'] : 0;
$numpnd = isset($datax['15'])? $datax['15'] : 0;
$numovr = isset($datax['20'])? $datax['20'] : 0;

$numOK=$numin - $numovr - $numout; // agent is in compliance and has no overrides //


echo "<div class=\"heading\">Policy Compliance</div>";

//* spacer *//
echo "<div class=\"spacer\"></div>";

// in
echo "<div class=\"minibox\">";
	echo "<div class=\"miniheading\">In Compliance</div>";
	echo "<div class=\"mininum\">";
	echo "<font color=\"#009933\">".$numOK."</font>";
	echo "</div>";
echo "</div>";

// override
echo "<div class=\"minibox\">";
	echo "<div class=\"miniheading\">Override</div>";
	echo "<div class=\"mininum\">";
	$color="#009933";
	if ($numovr > 0) { $color="orange"; }
	echo "<font color=\"".$color."\">".$numovr."</font>";
	echo "</div>";
echo "</div>";

// out
echo "<div class=\"minibox\">";
	echo "<div class=\"miniheading\">Not Compliant</div>";
	echo "<div class=\"mininum\">";
	$color="#009933";
	if ($numout > 0) { $color="red"; }
	echo "<font color=\"".$color."\">".$numout."</font>";
	echo "</div>";
echo "</div>";

// pending
echo "<div class=\"minibox\">";
	echo "<div class=\"miniheading\">Deploying</div>";
	echo "<div class=\"mininum\">";
	$color="#009933";
	if ($numpnd > 0) { $color="orange"; }
	echo "<font color=\"".$color."\">".$numpnd."</font>";
	echo "</div>";
echo "</div>";
 
  
// no policy
echo "<div class=\"minibox\">";
	echo "<div class=\"miniheading\">No Policy</div>";
	echo "<div class=\"mininum\">";
	$color="#009933";
	if ($nopolicy > 0) { $color="orange"; }
	echo "<font color=\"".$color."\">".$nopolicy."</font>";
	echo "</div>";
echo "</div>";
   
 
// if override or not compliant >0, list them...
if ($numovr<>0 or $numout<>0) {
  
  $stmt = sqlsrv_query( $conn, $tsql);
  if( $stmt === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }
  echo "<div class=\"datatable\">";
  echo "<table id=\"patchlist\">";
  echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colL\">Policy</th><th class=\"colM\">Status</th></tr>";
 
  while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
  {
    echo "<tr><td class=\"colL\">";
    showAgentIcon($row['online'],$row['currentLogin']); 
    echo "&nbsp;".$row['MachineId']."</td>";
    echo "<td class=\"colL\">".$row['policyName']."</td>";
    echo "<td class=\"colM\">".$row['policyStatus']."</td>";
    echo "</tr>";
  }
  echo "</table>";
  echo "</div>";
}

sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>