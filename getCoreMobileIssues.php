<?php 
$pageContent = null;
ob_start();
include 'dblogin.php';

$left=true;
$right=true;

$id=$_GET["type"];
if ($id=='l') { $right=false; }
if ($id=='r') { $left=false; }

// classic MDM = VSA 6.x, 7.0 & 8.0

if ($KVer < 9) {

$tsql = "select displayName, KMDM.CountryCodes.phonePrefix as phonePrefix, phoneNumber, isLost, isWiped, isLocked, alarmSounding
  from KMDM.vLostMobileDevices
  join KMDM.CountryCodes on KMDM.vLostMobileDevices.countrycode = KMDM.CountryCodes.code
  where isLost = 'true' or isWiped = 'true' or isLocked = 'true' or alarmSounding = 'true'";
 
$tsql2 = "select count(*) as num
  from KMDM.vLostMobileDevices
  where isLost = 'true' or isWiped = 'true' or isLocked = 'true' or alarmSounding = 'true'";
 


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
	 echo "<img src=\"images/cross.png\">";
 }
 echo "</td><td>{$row_count['num']} Mobile Device Issue";
 if ($row_count['num'] != 1) { echo 's'; }
 echo "</td></tr></table>";

 }
 
 
if ($right==true) {

echo "<div class=\"heading\">";
echo "Mobile Device Issues";
// echo "<div class=\"topn\">showing first ".$resultcount."</div>";
echo "</div>";

if ($row_count['num']!=0) {

  $stmt = sqlsrv_query( $conn, $tsql);
  if( $stmt === false )
  {
     echo "Error in executing query.<br/>";
     die( print_r( sqlsrv_errors(), true));
  }
  
  echo "<table id=\"mobilealarms\">";
  echo "<tr><th class=\"colL\">Mobile Name</th><th class=\"colL\">Mobile Number</th><th class=\"colL\">Reason</th></tr>";

  while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
  {
     echo "<tr><td class=\"colL\">".$row['displayName']."</td>";
	 echo "<td class=\"colL\">+".$row['phonePrefix']." ".$row['phoneNumber']."</td>";

	 echo "<td class=\"colL\">";
	 if ($row['isLost']=='True') echo "Lost ";
	 if ($row['isWiped']=='True') echo "Wiped ";
	 if ($row['isLocked']=='True') echo "Locked ";
	 if ($row['alarmSounding']=='True') echo "Alarm Sounding";	 
	 echo "</td></tr>";
  }

  echo "</table>";
}

}

}
sqlsrv_close( $conn );
$pageContent = ob_get_contents(); // collect above content and store in variable
ob_end_clean();
echo $pageContent;
?>