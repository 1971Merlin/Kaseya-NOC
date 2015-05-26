<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

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
  
sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>