<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';


//* patch policy members *//

$tsql = "SELECT distinct top ".$resultcount." vl.displayName as machName,
( STUFF((select ', ' + PolicyName from vPatchPolicyMember as st2
  where vppm.machineId = st2.MachineId
  order by st2.PolicyName
  FOR XML PATH(''), TYPE).value('.', 'NVARCHAR(MAX)'), 1, 2, '')
 ) PolicyName,
  missingApproved,pending,failed,
  rebootPending = case
    when rebootPending=0 then 'No'
    else 'Yes'
  end,
  lastPatchScan,testStatusDescription, vl.online as online, vl.currentLogin
  FROM vPatchPolicyMember as vppm";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = vppm.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on vppm.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql.=" join vAgentLabel vl on vl.agentGuid = vppm.agentGuid
  join vPatchStatusByAgent pp on pp.agentGuid = vppm.agentGuid
  where PolicyName not like '-No policy-' and (missingApproved >0 or failed >0)
  order by rebootPending desc, failed desc, missingApproved desc, pending desc";
?>
<div id="patchdialog"></div>
<script type="text/javascript">
$(document).ready(function() {
$( "#patchdialog" ).dialog({ autoOpen: false, dialogClass: "no-close" });
$( "#patchlist td:first-child" ).mouseover(function() { 
	$( "#patchdialog" ).dialog("option", "title", $(this).text());
	$.ajax({
		'type':'GET',
		'url': "getPatchInfoPopup.php?name="+$(this).attr('ref'),
		'cache':false,
		'success':function(data) {
			$("#patchdialog").html(data);
		}
	});
	$("#patchdialog").dialog("option", "minHeight", 50);
	$("#patchdialog").dialog("option", "maxHeight", 200);
	$('#patchdialog').css('overflow', 'auto');
	$("#patchdialog").dialog("option", "width", 'auto');
	$( "#patchdialog" ).dialog( "open" );
	$( "#patchdialog" ).dialog({ position: { my: "left top", at: "right middle", of: $(this), collision: "fit" } });
	}).mouseout(function() {
		$( "#patchdialog" ).dialog( "close" );
	});
});
</script>
<?php
  
$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

echo "<div class=\"heading\">";
echo "Patching Status (Patch Policy Members)";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";



echo "<div class=\"datatable\">";
echo "<table id=\"patchlist\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colL\">Patch Policies</th><th class=\"colL\">Missing</th><th class=\"colL\">Pending</th><th class=\"colL\">Failed</th><th class=\"colL\">Reboot Pending</th><th class=\"colL\">Last Checked</th><th class=\"colL\">Last Result</th></tr>";


while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
  echo "<tr><td class=\"colL\" ref=\"{$row['machName']}\">";
  showAgentIcon($row['online'],$row['currentLogin']); 
  echo "&nbsp;".$row['machName']."</td>";
  echo "<td class=\"colL\">".$row['PolicyName']."</td>";
  echo "<td class=\"colM\">".$row['missingApproved']."</td>";
  echo "<td class=\"colM\">".$row['pending']."</td>";

  echo "<td class=\"colM\">";
  if ($row['failed']>0){
    echo "<font color=\"red\">".$row['failed']."</font></td>";
  }
  else {
    echo $row['failed']."</td>";
  }

  
  echo "<td class=\"colM\">";
  if ($row['rebootPending']=="Yes") {
    echo "<font color=\"red\">".$row['rebootPending']."</font></td>";
  }
  else {
    echo $row['rebootPending']."</td>";
  }
  
  $dispdate = (isset($row['lastPatchScan']) ? date($datestyle." ".$timestyle,$row['lastPatchScan']->getTimestamp()) : 'Never');
  echo "<td class=\"colM\">".$dispdate."</td>";
  
  echo "<td class=\"colM\">";
  
  $pre="";
  $post="";
  
  if ($row['testStatusDescription']=="Untested") {
    $pre="<font color=\"orange\">";
	$post="</font>";
  }
  if ($row['testStatusDescription']=="Pending") {
    $pre="<i>";
	$post="</i>";
  }
  
  echo $pre.$row['testStatusDescription'].$post."</td></tr>";
}
echo "</table>";
echo "</div>";


//* spacer *//
echo "<div class=\"spacer\">";
echo "</div>";


//* non-patch policy members *//

$tsql = "SELECT distinct top ".$resultcount." vl.displayName as machName,PolicyName,missingApproved,pending,failed,
  rebootPending = case
    when rebootPending=0 then 'No'
    else 'Yes'
  end,
  lastPatchScan,testStatusDescription, vl.online as online, vl.currentLogin
  FROM vPatchPolicyMember";
if ($usescopefilter==true) { $tsql.=" join vdb_Scopes_Machines foo on (foo.agentGuid = vPatchPolicyMember.agentGuid and foo.scope_ref = '".$scope_filter."')"; }
if ($org_filter!="Master") { $tsql.=" 
 join dbo.DenormalizedOrgToMach on vPatchPolicyMember.agentGuid = dbo.DenormalizedOrgToMach.AgentGuid
  and dbo.DenormalizedOrgToMach.OrgId = (select id from kasadmin.org where kasadmin.org.ref = '".$org_filter."')"; }
  $tsql.=" join vAgentLabel vl on vl.agentGuid = vPatchPolicyMember.agentGuid
  join vPatchStatusByAgent pp on pp.agentGuid = vPatchPolicyMember.agentGuid
  where PolicyName = '-No policy-' and (missingApproved >0 or failed >0)
  order by rebootPending desc, failed desc, missingApproved desc, pending desc";

   
  
$stmt = sqlsrv_query( $conn, $tsql);
if( $stmt === false )
{
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
}

echo "<div class=\"heading heading2\">";
echo "Patching Status (No Patch Policy)";
echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";

echo "<div class=\"datatable\">";
echo "<table id=\"patchlist2\">";
echo "<tr><th class=\"colL\">Machine Name</th><th class=\"colL\">Patch Policy</th><th class=\"colL\">Missing</th><th class=\"colL\">Pending</th><th class=\"colL\">Failed</th><th class=\"colL\">Reboot Pending</th><th class=\"colL\">Last Checked</th><th class=\"colL\">Last Result</th></tr>";


while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
{
  echo "<tr><td class=\"colL\">";
  showAgentIcon($row['online'],$row['currentLogin']); 
  echo "&nbsp;".$row['machName']."</td>";
  echo "<td class=\"colL\">".$row['PolicyName']."</td>";
  echo "<td class=\"colM\">".$row['missingApproved']."</td>";
  echo "<td class=\"colM\">".$row['pending']."</td>";

  echo "<td class=\"colM\">";
  if ($row['failed']>0){
    echo "<font color=\"red\">".$row['failed']."</font></td>";
  }
  else {
    echo $row['failed']."</td>";
  }

  
  echo "<td class=\"colM\">";
  if ($row['rebootPending']=="Yes") {
    echo "<font color=\"red\">".$row['rebootPending']."</font></td>";
  }
  else {
    echo $row['rebootPending']."</td>";
  }
  
  $dispdate = (isset($row['lastPatchScan']) ? date($datestyle." ".$timestyle,$row['lastPatchScan']->getTimestamp()) : 'Never');
  echo "<td class=\"colM\">".$dispdate."</td>";
  
  echo "<td class=\"colM\">";
  
  $pre="";
  $post="";
  
  if ($row['testStatusDescription']=="Untested") {
    $pre="<font color=\"orange\">";
	$post="</font>";
  }
  if ($row['testStatusDescription']=="Pending") {
    $pre="<i>";
	$post="</i>";
  }
  
  echo $pre.$row['testStatusDescription'].$post."</td></tr>";
}

echo "</table>";
echo "</div>";


sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>