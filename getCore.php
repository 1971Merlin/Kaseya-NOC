<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<?php require_once 'dblogin.php'; ?>
<head>
<!-- jquery libraries -->
<script src="jquery-1.11.1.min.js" type="text/javascript"></script>

<!-- JqueryUI library -->
<link rel="stylesheet" type="text/css" href="jquery-ui-1.11.2/themes/smoothness/jquery-ui.css" >
<script src="jquery-ui-1.11.2/jquery-ui.js" type="text/javascript"></script>


  <script>
  $(function() {
    $( "#details" ).resizable();
	$( "#details" ).draggable();
  });
  </script>



<!-- must load my stylesheet last, to alter jQuery defaults -->
<link rel="stylesheet" type="text/css" href="mytech.css">
</head>
<body onload="refresh();">


<div class="panel" id="core" style="font-size:16px; padding:2px; width:265px; height:198px;">
	<div class="heading">Critical Status</div>
	<div id="leftbox">
		<div id="offlineServers" class="pointer"></div>
		<div id="ungroupedAgents" class="pointer"></div>
		<div id="pendingReboot" class="pointer"></div>
		<div id="suspendedAgents" class="pointer"></div>
		<div id="outdatedAgents" class="pointer"></div>
		<div id="pendingScripts" class="pointer"></div>
	</div> 
</div>


<div class="panel" id="details" style="padding:2px; width:600px; height:198px; position:relative;">
	<div class="heading" id="detailheading"><span>Details Pane</span>
		<div class="topn">showing first <?php echo $resultcount ?></div>
	</div>
	<div class=\"spacer\"></div>
	<div class="alertdetail" id="coredetails" style="float:left"></div>
	<div id="spinner" class="spinner" style="display:none;"><img id="img-spinner" src="images/spinner.gif" alt="Loading"/></div>
</div>


<div id="refrsh">
	<button onclick="refresh();">Refresh Page</button><br/>
	<button onclick="location.href = 'editsettings.php';">Edit Settings</button><br/>
	<button onclick="resetwindows();">Reset Layout</button>
</div>


<script type="text/javascript">



var paneldivs = ["#offlineServers","#ungroupedAgents","#pendingReboot","#suspendedAgents","#outdatedAgents","#pendingScripts"];
var panelphps = ["getcoreOfflineServers","getCoreUngroupedAgents","getcorePendingReboot","getCoreSuspendedAgents","getcoreOutdatedAgents","getCorePendingScriptApprovals"];

var max = paneldivs.length;
var current = max - 1; /* must be -1 as first refresh rolls over before display */
var tm;


$(document).ready(function() {
  
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
		var foo = $("#leftbox div").index( this ) ;
		console.log(foo);
		current = foo;
		clearAll(); 
		highlightSelected();
		loadpanel(paneldivs[current],panelphps[current] + '.php','b');
		clearTimeout(tm);
		tm = setTimeout('refresh();',10000);
	  });
	}

  }
  
);
  
  
function clearAll() {
	$('#leftbox > div').each(function(index){ $(this).css({"background-color" : "white"}); $(this).css({"font-weight" : "normal"}); } );
}


function highlightSelected() {
	$('#leftbox > div').eq(current).css({"background-color" : "yellow"});
	$('#leftbox > div').eq(current).css({"font-weight" : "bold"});
}


function refresh() {
	clearTimeout(tm);
    current++;
    if (current == max)  { current = 0; }	
	clearAll();
	highlightSelected();
	for (var i = 0; i < paneldivs.length; i++) {
		loadpanel(paneldivs[i],panelphps[i]+".php",'l');
	}
	loadpanel(paneldivs[current],panelphps[current]+".php",'b');
    tm = setTimeout('refresh();',10000);
}	

  
function loadpanel(divname,scriptname,type) {  
  $(divname).load(scriptname + "?type=" + type + "&rnd="+ new Date().getTime(), function(response, status, xhr) {
    if (status == "error") {
      var msg = "Sorry but there was an error: ";
      $(divname).html(msg + xhr.status + " " + xhr.statusText);
    }
  });
}
  
</script>

</body>
</html>