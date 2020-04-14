<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<?php require_once 'dblogin.php'; ?>
<head>
<title><?php echo $NOCtitle;?></title>
<meta http-equiv="X-UA-Compatible" content="IE=9" >
<meta name="description" content="NOC Status Display for Kaseya">
<meta name="viewport" content="width=device-width, initial-scale=1,  user-scalable=yes" />

<!-- jquery libraries -->
<script src="jquery-3.3.1.min.js" type="text/javascript"></script>

<!-- cookie library -->
<script src="jcookie1.4.1/jquery.cookie.js"></script>

<!-- charting libraries -->
<script src="highcharts8.0.4/highcharts.js"></script>
<script src="highcharts8.0.4/highcharts-3d.js"></script>
<script src="highcharts8.0.4/highcharts-more.js"></script>
<script src="highcharts8.0.4/modules/timeline.js"></script>

<!-- Jquery UI library -->
<link rel="stylesheet" type="text/css" href="jquery-ui-1.12.1/jquery-ui.css">
<script src="jquery-ui-1.12.1/jquery-ui.js" type="text/javascript"></script>




<!-- must load my stylesheet last, to alter jQuery defaults -->
<link rel="stylesheet" type="text/css" href="main.css">
<link rel="stylesheet" type="text/css" media="only screen and (max-width: 500px)" href="mobilep.css" />
<link rel="stylesheet" type="text/css" media="only screen and (min-width: 501px) and (max-width: 899px)" href="mobilel.css" />
<link rel="stylesheet" type="text/css" media="only screen and (min-width: 900px) and (max-width: 1150px)" href="tablet.css" />
</head>


<body onload="refresh();">
<script type="text/javascript">

//window.onerror = function (errorMsg, url, lineNumber) {
//    alert('Error: ' + errorMsg + ' Script: ' + url + ' Line: ' + lineNumber);
//}

  $(document).ready(function() {

    $.cookie.defaults = { path: '/', expires: 3650 };

 // make them draggable and resizable
	$( "#details" ).resizable();
	$( "#showRSS" ).resizable();
	$( "#showEXT" ).resizable();
	
	$( "#row1 > .panel" ).draggable({ containment: "#row1", scroll: true }, { stack: "#row1 > .panel" });
	$( "#row2 > .panel" ).draggable({ containment: "#row2", scroll: true }, { stack: "#row2 > .panel" });

	
  // if position saved in a cookie, then position the panel
  
	$('#row1 > .panel').each(function(index){
		var foo = $(this).attr("id");
		if ($.cookie(foo) != null) {
			var left = JSON.parse($.cookie(foo)).left;
			var top = JSON.parse($.cookie(foo)).top;
			var z = JSON.parse($.cookie(foo)).zindex;
			$("#"+foo).css("left", left + "px");
			$("#"+foo).css("top", top + "px");
			$("#"+foo).css("zIndex", z );
		};
		
		var foosize = foo + ".size";
		if ($.cookie(foosize) != null) {
			var width = JSON.parse($.cookie(foosize)).width;
			var height = JSON.parse($.cookie(foosize)).height;
			$("#"+foo).css("width", width + "px");
			$("#"+foo).css("height", height + "px");
		}
	});  


	$('#row2 > div').each(function(index){
		var foo = $(this).attr("id");
		if ($.cookie(foo) != null) {
			var left = JSON.parse($.cookie(foo)).left;
			var top = JSON.parse($.cookie(foo)).top;
			var z = JSON.parse($.cookie(foo)).zindex;
			$("#"+foo).css("left", left + "px");
			$("#"+foo).css("top", top + "px");
			$("#"+foo).css("zIndex", z);
		};
	
	});  
	
	

  
    $(document).ajaxStart(function(){
          $("#spinner").show();
        });
    $(document).ajaxStop(function(){
          $("#spinner").hide();
        });
    $(document).ajaxError(function(){
          $("#spinner").hide();
        });

	

	for (var i = 0; i < paneldivs.length; i++) {
	  $(paneldivs[i]).on('click', function() {
		current = $("#leftbox div").index( this ) ;
		clearAll(); 
		highlightSelected();
		loadCorePanel(paneldivs[current],'#coredetails',panelphps[current] + '.php','b');
		clearTimeout(tm);
		tm = setTimeout('refresh();',10000);
	  });
	}

  	

// draggable save positions
	 
     $(function () {
         $("#row1 > .panel, #row2 div").draggable({ stop: function (event, ui) {
				val = ui.position;
				val["zindex"] = ui.helper.css('zIndex');
          		$.cookie( $(this).attr("id"), JSON.stringify(val) );			
			}
		});
     });  
	 

    $(function () {
         $("#details, #showRSS, #showEXT").resizable({ stop: function (event, ui) {;
				var val = ui.size;
          		$.cookie( $(this).attr("id") + ".size", JSON.stringify(val) );				
			}
		});
     });  

	 
}); 
	 
	 // delete all cookies thus deleting all panel positions; z-index 
	 function resetwindows() {
	$('#row1 > .panel').each(function(index){
		var foo = $(this).attr("id");
				$.cookie(foo,null);
				$.removeCookie(foo);
				$("#"+foo).css("left", 'auto');
				$("#"+foo).css("top", 'auto');
				$("#"+foo).css("width", '');
				$("#"+foo).css("height", '');
		var foo = $(this).attr("id") + ".size";
				$.cookie(foo,null);
				$.removeCookie(foo);
				});	

	$('#row2 > div').each(function(index){
		var foo = $(this).attr("id");
				$.cookie(foo,null);
				$.removeCookie(foo);
				$("#"+foo).css("left", 'auto');
				$("#"+foo).css("top", 'auto');
		});	

		}
</script>


<!-- Page Header - logo, title, date/time etc. -->
<div id="topbox">
  <div id="header"><img src="images/logo.jpg" alt="NOC" align="left"><?php echo $NOCtitle;?></div>
  <div id="agentcounts"></div>
  <div id="dtime"></div>
</div>
    
<!-- Page Body; this is the bit that can be scrolled vertically  -->
<div id="mainbody">

<!-- top row, fixed panels -->
<div id="row1">

<!-- refresh panel -->
<div id="refrsh">
	<button onclick="refresh();">Refresh Page</button><br/>
	<button onclick="location.href = 'editsettings.php';">Edit Settings</button><br/>
	<button onclick="resetwindows();">Reset Layout</button>
</div>



<!-- core panels -->
<div class="panel" id="core">
	<div class="heading" id="coreheading">Critical Status</div>
	<div id="leftbox">
<?php if ($config['strip']['showOlServers'] == true) { echo "<div id=\"offlineServers\" class=\"pointer\"></div>"; } ?>
<?php if ($config['strip']['showUngrouped'] == true) { echo "<div id=\"ungroupedAgents\" class=\"pointer\"></div>"; } ?>
<?php if ($config['strip']['showPendReboot'] == true) { echo "<div id=\"pendingReboot\" class=\"pointer\"></div>"; } ?>
<?php if ($config['strip']['showSuspended'] == true) { echo "<div id=\"suspendedAgents\" class=\"pointer\"></div>"; } ?>
<?php if ($config['strip']['showOldAgents'] == true) { echo "<div id=\"outdatedAgents\" class=\"pointer\"></div>"; } ?>
<?php if ($config['strip']['showPendApprove'] == true) { echo "<div id=\"pendingScripts\" class=\"pointer\"></div>"; } ?>
<?php if ($config['strip']['showLastCheckin'] == true) { echo "<div id=\"lastCheckedIn\" class=\"pointer\"></div>"; } ?>
<?php if ($config['strip']['showMobile'] == true) { echo "<div id=\"mobileIssues\" class=\"pointer\"></div>"; } ?>
<?php if ($config['strip']['showScripts'] == true) { echo "<div id=\"activeScripts\" class=\"pointer\"></div>"; } ?>
	</div> 
</div>


<div class="panel" id="details">
	<div class="alertdetail" id="coredetails"></div>
	<div id="spinner" class="spinner" style="display:none;"><img id="img-spinner" src="images/spinner.gif" alt="Loading"/></div>
</div>

<!-- other 'top' panels -->

<?php if ($config['strip']['showSecurity'] == true) { echo "<div id=\"securitynums\" class=\"panel\"></div>"; } ?>
<?php if ($config['strip']['showAV'] == true) { echo "<div id=\"AVnums\" class=\"panel\"></div>"; } ?>
<?php if ($config['strip']['showBUDR'] == true) { echo "<div id=\"budrnums\" class=\"panel\"></div>"; } ?>
<?php if ($config['strip']['showPolicy'] == true) { echo "<div id=\"polnums\" class=\"panel\"></div>"; } ?>
<?php if ($config['strip']['showOlGraph'] == true) { echo "<div id=\"gphagents\" class=\"panel\"></div>"; } ?>
<?php if ($config['strip']['showRCHistory'] == true) { echo "<div id=\"gphRChistory\" class=\"panel\"></div>"; } ?>
<?php if ($config['strip']['showVSAUsers'] == true) { echo "<div id=\"loggedinusers\" class=\"panel\"></div>"; } ?>
<?php if ($config['strip']['showEXT'] == true) { echo "<div id=\"showEXT\" class=\"panel\"><div id=\"extdetails\"></div></div>"; } ?>
<?php if ($config['strip']['showRSS'] == true) { echo "<div id=\"showRSS\" class=\"panel\"><div id=\"rssdetails\"></div></div>"; } ?>

</div>


 <!-- second row: the rest of the panels that can be repositioned -->
<div class="row2" id="row2">

<!-- <div id="UptimeChart" class="panel"></div> -->



<?php if ($config['panels']['showCounts'] == true) { echo "<div id=\"agenttypes\" class=\"panel\"></div>"; } ?>
<?php if ($config['panels']['showRC'] == true) { echo "<div id=\"RCinfo\" class=\"panel\"></div>"; } ?>
<?php if ($config['panels']['showUptime'] == true) { echo "<div id=\"uptime\" class=\"panel\"></div>"; } ?>
<?php if ($config['panels']['showMGS'] == true) { echo "<div id=\"dotstatus\" class=\"panel\"></div>"; } ?>
<?php if ($config['panels']['showPatching'] == true) { echo "<div id=\"patching\" class=\"panel\"></div>"; } ?>
<?php if ($config['panels']['showSM'] == true) { echo "<div id=\"sm\" class=\"panel\"></div>"; } ?>
<?php if ($config['panels']['showAlarms'] == true) { echo "<div id=\"alarms\" class=\"panel\"></div>"; } ?>
<?php if ($config['panels']['showPolicy'] == true) { echo "<div id=\"policy\" class=\"panel\"></div>"; } ?>
<?php if ($config['panels']['showSD'] == true) { echo "<div id=\"sdesk\" class=\"panel\"></div>"; } ?>
<?php if ($config['panels']['showLowDisk'] == true) { echo "<div id=\"lowdisk\" class=\"panel\"></div>"; } ?>
<?php if ($config['panels']['showAv'] == true) { echo "<div id=\"security\" class=\"panel\"></div>"; } ?>
<?php if ($config['panels']['showKAV'] == true) { echo "<div id=\"KAV\" class=\"panel\"></div>"; } ?>
<?php if ($config['panels']['showSEC'] == true) { echo "<div id=\"SEC\" class=\"panel\"></div>"; } ?>
<?php if ($config['panels']['showSEP'] == true) { echo "<div id=\"SEP\" class=\"panel\"></div>"; } ?>
<?php if ($config['panels']['showBUDR'] == true) { echo "<div id=\"BUDR\" class=\"panel\"></div>"; } ?>
<?php if ($config['panels']['showBUDR'] == true) { echo "<div id=\"BUgraph\" class=\"panel\"></div>"; } ?>
<?php if ($config['panels']['showKCB'] == true) { echo "<div id=\"KCB\" class=\"panel\"></div>"; } ?>


	</div>
</div>

<!-- Page Footer, VSA stats etc. -->
<div id="bottombox">
  <div id="leftstat">&nbsp;VSA Server Stats&nbsp;</div>
  <div id="rightstat"></div>
  <div id="NOCconfig"></div>
</div>

<!-- Non-page layout related Code -->

<script type="text/javascript">

<?php
$paneldivs = array();
$panelphps = array();

if ($config['strip']['showOlServers'] == true) { $paneldivs[]="#offlineServers"; $panelphps[]="getcoreOfflineServers"; }
if ($config['strip']['showUngrouped'] == true) { $paneldivs[]="#ungroupedAgents"; $panelphps[]="getcoreUngroupedAgents"; }
if ($config['strip']['showPendReboot'] == true) { $paneldivs[]="#pendingReboot"; $panelphps[]="getcorePendingReboot"; }
if ($config['strip']['showSuspended'] == true) { $paneldivs[]="#suspendedAgents"; $panelphps[]="getCoreSuspendedAgents"; }
if ($config['strip']['showOldAgents'] == true) { $paneldivs[]="#outdatedAgents"; $panelphps[]="getCoreOutdatedAgents"; }
if ($config['strip']['showPendApprove'] == true) { $paneldivs[]="#pendingScripts"; $panelphps[]="getCorePendingScriptApprovals"; }
if ($config['strip']['showLastCheckin'] == true) { $paneldivs[]="#lastCheckedIn"; $panelphps[]="getCoreLastCheckedIn"; }
if ($config['strip']['showMobile'] == true) { $paneldivs[]="#mobileIssues"; $panelphps[]="getCoreMobileIssues"; }
if ($config['strip']['showScripts'] == true) { $paneldivs[]="#activeScripts"; $panelphps[]="getCoreActiveScripts"; }



echo "var paneldivs = [";
$first = true;
foreach ($paneldivs as $foo) { 
	if (!$first==true) echo ",";
	echo "\"$foo\"";
	$first=false;
}
echo "];\r\n";

echo "var panelphps = [";
$first = true;
foreach ($panelphps as $foo) { 
	if (!$first==true) echo ",";
	echo "\"$foo\"";
	$first=false;
}
echo "];";
?>

var max = paneldivs.length;
var current = max - 1; /* must be -1 as first refresh rolls over before display */
var tm;		/* timeout to refresh div1 */
var tm2;	/* timeout to refresh div2 */

 
function clearAll() {
	$('#leftbox > div').each(function(index){ $(this).css({"background-color" : "transparent"}); $(this).css({"font-weight" : "normal"}); } );
}

function highlightSelected() {
	$('#leftbox > div').eq(current).css({"background-color" : "yellow"});
	$('#leftbox > div').eq(current).css({"font-weight" : "bold"});
}

function refresh() {
	refreshdiv1();
	refreshdiv2();
}

function refreshdiv1() {
	clearTimeout(tm);
    current++;
    if (current == max)  { current = 0; }	
	clearAll();
	highlightSelected();
	for (var i = 0; i < paneldivs.length; i++) {
		loadCorePanel(paneldivs[i],'#coredetails',panelphps[i]+".php",'l');
	}
	//dont try to refresh if no panels enabled
	if (max>0) { loadCorePanel(paneldivs[current],'#coredetails',panelphps[current]+".php",'r'); }
	tm = setTimeout('refreshdiv1();',10000);
}

function refreshdiv2() {
	clearTimeout(tm2);
	loadpanel("#rightstat","getKServerStats.php");
	loadpanel("#NOCconfig","getNocConfig.php");
	loadpanel("#dtime","datetime.php");  
	loadpanel("#agentcounts","getAgentCounts.php");
<?php if ($config['strip']['showOlGraph'] == true) { echo "loadpanel(\"#gphagents\",\"getOnlineAgents.php\");"; } ?>
<?php if ($config['strip']['showRCHistory'] == true) { echo "loadpanel(\"#gphRChistory\",\"getRChistory.php\");"; } ?>
<?php if ($config['strip']['showVSAUsers'] == true) { echo "loadpanel(\"#loggedinusers\",\"getLoggedInUsers.php\");"; } ?>
<?php if ($config['strip']['showSecurity'] == true) { echo "loadpanel(\"#securitynums\",\"getSecurity.php\");"; } ?>
<?php if ($config['strip']['showAV'] == true) { echo "loadpanel(\"#AVnums\",\"getKAV.php\");"; } ?>
<?php if ($config['strip']['showBUDR'] == true) { echo "loadpanel(\"#budrnums\",\"getBUDR.php\");"; } ?>
<?php if ($config['strip']['showPolicy'] == true) { echo "loadpanel(\"#polnums\",\"getPolicyStats.php\");"; } ?>
<?php if ($config['strip']['showLastCheckin'] == true) { echo "loadpanel(\"#lastcheck\",\"getLastCheckedIn.php\");"; } ?>
<?php if ($config['strip']['showEXT'] == true) { echo "loadpanel(\"#extdetails\",\"getExternalURL.php\");"; } ?>		
<?php if ($config['strip']['showRSS'] == true) { echo "loadpanel(\"#rssdetails\",\"getRSSFeed.php\");"; } ?>


loadpanel("#UptimeChart","getUptimeChart.php");

<?php if ($config['panels']['showCounts'] == true) { echo "loadpanel(\"#agenttypes\",\"getAgentTypeCounts.php\");"; } ?>
<?php if ($config['panels']['showUptime'] == true) { echo "loadpanel(\"#uptime\",\"getUptime.php\");"; } ?>
<?php if ($config['panels']['showMGS'] == true) { echo "loadpanel(\"#dotstatus\",\"getDotStatus.php\");"; } ?>
<?php if ($config['panels']['showPatching'] == true) { echo "loadpanel(\"#patching\",\"getPatchInfo.php\");"; } ?>
<?php if ($config['panels']['showSM'] == true) { echo "loadpanel(\"#sm\",\"getSMInfo.php\");"; } ?>
<?php if ($config['panels']['showAlarms'] == true) { echo "loadpanel(\"#alarms\",\"getalarms.php\");"; } ?>
<?php if ($config['panels']['showPolicy'] == true) { echo "loadpanel(\"#policy\",\"getPolicy.php\");"; } ?>
<?php if ($config['panels']['showSD'] == true) { echo "loadpanel(\"#sdesk\",\"getSDtickets.php\");"; } ?>
<?php if ($config['panels']['showLowDisk'] == true) { echo "loadpanel(\"#lowdisk\",\"getlowdisk.php\");"; } ?>
<?php if ($config['panels']['showAv'] == true) { echo "loadpanel(\"#security\",\"getSecurityInfo.php\");"; } ?>
<?php if ($config['panels']['showKAV'] == true) { echo "loadpanel(\"#KAV\",\"getKAVinfo.php\");"; } ?>
<?php if ($config['panels']['showSEC'] == true) { echo "loadpanel(\"#SEC\",\"getSEC.php\");"; } ?>
<?php if ($config['panels']['showSEP'] == true) { echo "loadpanel(\"#SEP\",\"getSEPinfo.php\");"; } ?>
<?php if ($config['panels']['showBUDR'] == true) { echo "loadpanel(\"#BUDR\",\"getBUDRstatus.php\");"; } ?>
<?php if ($config['panels']['showBUDR'] == true) { echo "loadpanel(\"#BUgraph\",\"getBackupTimes.php\");"; } ?>
<?php if ($config['panels']['showRC'] == true) { echo "loadpanel(\"#RCinfo\",\"getRCdetails.php\");"; } ?>
<?php if ($config['panels']['showKCB'] == true) { echo "loadpanel(\"#KCB\",\"getCloudBU.php\");"; } ?>


	tm2 = setTimeout('refreshdiv2();',60000);
}


function loadpanel(divname,scriptname) {  
  $(divname).load(scriptname + "?rnd="+ new Date().getTime(), function(response, status, xhr) {
    if (status == "error") {
      var msg = "Sorry but there was an error: ";
      $(divname).html(msg + xhr.status + " " + xhr.statusText);
    }
  });
}

function loadCorePanel(divnameL,divnameR,scriptname,typ) {  
	if (typ=='l' || typ=='b') {
		$(divnameL).load(scriptname + "?type=l" + "&rnd="+ new Date().getTime(), function(response, status, xhr) {
			if (status == "error") {
				var msg = "Sorry but there was an error: ";
				$(divname).html(msg + xhr.status + " " + xhr.statusText);
			}
		});
	}

	if (typ=='r' || typ=='b') {
		$(divnameR).load(scriptname + "?type=r" + "&rnd="+ new Date().getTime(), function(response, status, xhr) {
			if (status == "error") {
				var msg = "Sorry but there was an error: ";
				$(divname).html(msg + xhr.status + " " + xhr.statusText);
			}
		});
	}

}

</script>
</body>
</html>