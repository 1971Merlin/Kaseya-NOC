This project is no longer maintained as at January, 2022. This is becuase I no longer use/have access to the Kaseya Platform.

Thank you for your support over the years; this project will remain read only/archived for future use.

------------------------------------------------------------------------------------------------------------------------------------







Welcome, Kaseya user!

Herein be the Install Instructions.

------------------------------------------------------------------------------------
New install:
------------------------------------------------------------------------------------

You do *NOT* install this on your K SERVER!!!!!!!!

You will require an on-premise install of K server - the SaaS / cloud version won't work, as Kaseya don't allow direct connections to their SaaS SQL servers.

You will need a machine that can connect directly to the SQL database on your K server (TCP Port 1433); therefore you will need a machine on the same LAN or a VPN link to your K server - do not expose SQL to the world unless you like being hacked and DDOSd constantly!

Similarly, your K server's SQL instance must be contactable from the LAN - so open up your SQL firewall port on your K server and make sure SQL is listening to the LAN (not just on localhost!). if you can telnet to port 1433 on your K server from the proposed machine, you should be OK.


OK, good to go? Lets get started.

First, Install an Apache web server with PHP Extensions. I used a pre-built package called XAMPP on my laptop, but almost any pre-built apache/php package or handbuilt system will do. PHP 5.4 or later is strongly recommended as this is what I have developed with. I don't use the MySQL part of XAMPP, so you can remove/disable or just ignore the MySQL part if you wish.

I am currently using XAMPP 5.6.3 VC11 x86 edition.

Install any prerequisites that XAMPP requires - in my case this was the Microsoft Visual C++ runtimes 2012 x86.

I'll say it again. Don't install this on your K server - use a spare workstation or dedicated machine. Ensure nothing else is running on port 80 (i.e. don't install IIS or other web server or software that uses port 80).


Prerequisites - Setup your web server and configure MS-SQL library & set timezone.



WINDOWS OS:

Install the Microsoft SQL add-ons for PHP and the ODBC Driver 11.


SQL Server Add-ons for PHP: 	http://www.microsoft.com/en-au/download/details.aspx?id=20098
ODBC Driver 11: 		http://www.microsoft.com/en-us/download/details.aspx?id=36434

note: We are using the 3.2 edition of the MS SQL extensions, that work with PHP 5.4, 5.5 and 5.6 so you want to download SQLSRV32.EXE and unzip it to a temp folder.

to install the SQL add-ons into php, edit the php.ini file and add the following line: extension=php_sqlsrv_56_ts.dll to the dynamic extensions section.

then copy php_sqlsrv_56_ts.dll to the PHP extensions folder (c:\xampp\php\ext) - you can then delet ethe temp folder you unzipped eariler.

Whilst editing php.ini, set the default timezone to your timezone, by altering the line date.timezone = Australia/Adelaide to whatever is correct for your location.




LINUX OS:

You'll need to Use FreeTDS to connect to MS-SQL instead of the microsoft Extensions.

http://www.freetds.org/

Replace all SQL calls throught with the FreeTDS equivilants throughout all the code.

------------------------------------------------------------------------------------

Installation and configuration of NOC site:

Make a website folder within Apache's WWW space (you can put it in the root, a subfolder or whatever - default is c:\xampp\htdocs) and unzip all the files from the archive into it. 


Edit your dblogin.php, be sure to set the correct default timezone in php.ini - use the same timezone as your K server. This will make times in the NOC match what you see in K - in my case date_default_timezone_set('Australia/Adelaide'); (use whatever timezone you're in of course!). NB: The NOC code doesn't adjust for the timezone of the browser, so if the browser and the NOC are in different timezones, the times will most likely be different.


Load the index.php in your browser (http://localhost/your_folder/index.php). The system will detect an error and point you to the config page. Visit the config page and enter your server details -> these are saved into the noc.ini file. Enjoy!

If your settings are wrong, you'll be directed back to the config page. you can also visit the config page by clicking the settings button on the main page.


security note: since the noc.ini file contains an SQL password in plaintext, ensure you secure your NOC carefully if you choose to make it public-facing. Securing PHP is beyond the scope of this guide, use your common sense!! but for example, you may want to protect the whole NOC site with a .htaccess file to prevent access to unauthorized users.


------------------------------------------------------------------------------------
Upgrading to a new version:
------------------------------------------------------------------------------------

Overwrite all the existing files with the new versions, but remember to edit the noc.ini and re-insert your server name, credentials, timezone, Scope and ORG settings (copy them from the existing noc.ini if unsure).

If a new version of jquery or jqueryUI is supplied, you can delete the old version.

If you have customized any individual files, remember to back these up and apply your customizations to the new release versions aferwards.

------------------------------------------------------------------------------------

notes and troubleshooting:

This NOC was initially developed based on the Kaseya 6.3 code base, in the second half of 2013. I have now upgraded to VSA R9, and all future development will be built around the current Kaseya release. I will continue to build based on the 'latest' releases as they come out, but I will also attempt to provide reasonable backwards compatability with earlier versions where feasible.

Currently full support for VSA 6.3, 6.5, R8 and R9 is present, although I don't have anything older than VSA 7.0 to test against these days.

The backup panel works with the BUDR module (Acronis engine) - if you don't have BUDR, it will probably give errors.

The antivirus panel works with the KES release 2.3 module (AVG 2012) - if you don't have this module, or have an older relase of KES, it will probably give errors.

If you have VSA 6.2 but not KMDM, the mobile related panels will give errors. Either add KMDM, or try to ignore the errors!

To remove panels you don't want, visit the config page, where you can disable each panel as you see fit.


The SMART status panel and Security Centre panels won't show any data at all for most users. These panels require some custom extensions to the VSA that i have developed - namely a group of agent procedures, some executables and some custom fields in the database. Contact me if you are interrsted in adding these features to your VSA.


If you get the error "Fatal error: Call to undefined function sqlsrv_connect() in C:\xampp\htdocs\kaseyaNOC\dblogin.php on line x" then you didn't install the SQL add-ons for PHP correctly.

If you get no errors but the scrren is totally blank, you probably didn't install the SQL Native client.

if you want the 'recent logs' panels to be shorter or taller, you can set the 'recent items count' on the config page.

------------------------------------------------------------------------------------

support:

As of January, 2022. This code is unsupported, as I no longer work with Kaseya.

------------------------------------------------------------------------------------

Version history:

1.0	Initial release

1.1	Added additional panels, IE refresh big fixed

1.2	3D look, additional info in panels, bottom server status bar, fixes for firefox, improved documentation
	fix for incorrect time offset in some panels

1.3	config page + noc.ini configfile replaces hand editing PHP files. Can edit noc.ini by hand also if desired.
	'master' view support to show ALL agents system-wide (similar to Kaseya's Master role).
	Scope and ORG support, drag/reposition panel support. More data on status bar. enable/disable panels support.
	graph of online agents and remote control history.
	many, many bugfixes and improvements. Major overhaul.

1.4	adjust low disk to ignore system, recovery and tools partitions
	adjust low disk panel for Mac Agent compatibility -> no drive letters on Macs
	fix issue with dblogin.php and invalid module ID's -> not installed
	add select distinct to queries, to fix duplicate counts when multiple scopes exist
	add fix all queries to support new format org and scope selection
	add title bar config in INI file
	add backup panel 'status last 24 hours' with more details
	add more detail to backup logs last 24 hrs
	add machine group status panel (dots panel) emulating classic kaseya dashboard
	add date/time formatting options - can set m/d/y, d/m/y (or any othe combo) and 12/24 hour options
	remove seconds from most screens - was wasting too much scrren realestate and not contributing any value
	fix getdate() replace with getutcdate() in Alarms panel -> corrects for UTC timezone
	add detect agents suspended for patching (as well as full suspend and monitor suspend)
	fix dots panel to use same rules for low disk, as main low disk panel (ignore recovery partitions)
	add Service Desk Panel with basic info re: open tickets in SD
	fix issues if noc.ini is incomplete/corrupt
	fix IE10 rendering issues (enable ie9 compatability mode)
	Optimize some panels by avoiding unnecessary SQL calls when we know that zero results will be returned
	jquery 1.10.2 library update

1.5	support new hotfix version number system as of 6.5.0.1 (v6.3 and older version hotfix system still supported)
	config screen - made scope.org selection window size fixed & scrollable to reduce page scrolling with large lists
	support ability to turn off each of the top row of panels, if desired
	changed config screen radio buttons to checkboxes - more efficient to code & makes more sense from a user perspective
	added 'enable/disable all' checkbox to config panels
	added 'mouseover' popup for long backup log texts -> read full text of message by hovering mouse over abbreviated text
	updated jqueryUI version to 1.10.4
	added policy compliance panel -> with thanks to Kaseya Community user "Josh" for base code
	Added "hotfix" vs "patch level" text (6.3 / 6.5 terminology) to Stats bar
	Changed panel order to allow more optimal screen fill


1.6

Core
	fixed w3c validation errors in code throughout all panels
	Fixed issue with refreshing panels that are not enabled; speeds up page refresh by removing unnecessary calls to php files
	added ability to save dragged window positions using cookies; use the reset button to reset layout to defaults
	Both the 'top row' and the 'main' panels are now draggable
	added change to title on browser tab for offline servers indication
	updated to different charting library with better & more flexible graphing capability
	panels which you don't have the K module for are automatically disabled (visit config page once to apply!!)
	updated jquery version to 1.11.1

BUDR
	added backup type Full/Incremental/Differential/Synthetic Full to BUDR Status Panel
	fixed bug counting successful & cancelled BUDR backups
	fixed bug dealing with BUDR status when a backup is in progress
	added backups in progress info to BUDR status panel
	added BUDR status now summarizes results in big numbers at top of list

KAV
	added preliminary KAV module (Kaspersky) support

KES
	fixed SQL to better display agents with issues -> Active Threats, Agent Online but with outdated AV definition

Patch
	patchinfo module handles multiple patch policies better - list all policies on one line instead of one line per policy

Agent Info
	added detailed agent counts panel with OS info service pack level, OS name, graphs etc.




1.7	fixed bug with get suspended agents count
	fixed bug with some icons in firefox (path wrong / character)
	fixed bug with machine group status panel when org=master and scope <>master
	fixed OS X build number
	fixed not closing SQL connection at end of each panel
	fixed javascript memory leaks
	fixed KBU graph time range, borders
	fixed graphing not destroying old chart before create new one [memory leak]
	improved popup for agent types panel with more efficient code
	added panel for scripts pending approval (VSA 7.0 or later)


1.8	potential bug in servers/WS counts in top bar fixed
	getofflineagents.php renamed to getOfflineServers.php and SQL code corrected to match other panels
	implemented 'proper' Scope and Organization selection
	RC History now supports VSA 7/ KRC (session count only, no minutes)
	fixed bug with SQL join in getalarms.php highlighted by new scope/org code
	combined KBU logs and Status screens to make better use of screen realestate
	Added panel to display external webcam/image source - monitor your security camera via the NOC
	Improved policy panel with large numbers displaying in/override/out of compliance status
	added icons for agents suspended and never checked in across all panels
	updated server uptimes to support suspended and never cecked in agents
	Added  new panels to top row with key stats - Security (KES), KBU, Policy
	fixed config screen to not check disabled panels when toggling with enable/disable all checkbox
	added popup info on patching details- shows patches failed and causing pending reboot
	fixed some code isues with popups
	updated JqueryUI library
	updated graphing library - fixes 3D pie drawing bug
	fixed bug with server uptimes for servers checking in for the first time / template accounts
	fixed bug with ORG type selection - now works even if no user-defined ORGs exist
	full re-code of scope/ORG selection (again!) - you can now select the unnamed and kserver groups (and they work)!
	optimized server uptimes screen by doing everything with a single SQL query
	added R8 support for KRC logging -> support new R8 logfile system


1.9	fixed sort order issues with getserveruptimes.php
	added RC details panel - see detailed RC history of VSA users, including current active KRC sessions, type etc.
	added agents not checking-in >30 days panel
	fixed issues with policy - counts now match the VSA
	fixed disable show pending script approvals tickbox on VSA <7 config page
	added 'showing first n' result filter to various panels to prevent excessively long tables of data
	added more details to Machine Group Status display - break down alarms by type as per Kaseya 'dot status' dashboard
	removed date filtering from parts of machine group status display to be consistent (this panel is not filtered by date/time)
	added detect if newer patch is available and show on footer bar (VSA 7.0+)
	added enhancements to KAV panel - server license stats now shown
	fixed sorting of RC sessions history
	many, many minor bugfixes & cleanups throughout
	updated jqueryui library 1.11.2



1.10	filter RC details by selected Scope and ORG (KRC R8+ only; VSA v7 and classic RC are unfiltered)
	Added admin's name to RC history pie chart labels
	impoved test for logged in VSA users (this info is buggy in VSA R7+)
	fixed pie charts for os types if 0 agents of type server or workstation in scope
	fixed VSA version detection code to global variable - standardized code
	support VSA R9: remove classic mobile management support if VSA R9+ (tables no longer exist in database)
	fixed pendingreboot - 'showing first n' appears even if num = 0 
	fixed security (AVG) infection counts to match VSA (don't include threats marked as excluded)
	added security (AVG) active threats count numbers
	added policy: marked for deployment status
	added serveruptimes % figure last 7 days
	added security (AVG) threat & vault top 5 charts
	added Anti-virus (KAV) threat & vault top 5 charts
	added Anti-Virus (KAV) top status summary panel
	changed default refresh to 1 minute (to reduce SQL load)
	fixed display formatting issues with backup activities graph - height jumping on refresh, axis labels & grid
	fixed bug in editsettings where can't save config if kDB unreachable



2.0	Trim server uptimes panel to top n/2 and bottom n/2
	moved control buttons in top RHS corner regardless of # of panels displayed
	introduced new 'core' statistics panels with automatic refresh of information displayed
	 - details pane for core info made resizable with size saved via cookie
	 - 'core' panels refresh set to 10 seconds
	 - 'other' panels refresh set to 60 seconds (was 30 secs previously)
	fixed bug in getbackuptimes.php causing error if no data to graph
	fixed bug in getonlineagents.php causing error if no data to graph
	fixed bug in getSecurityInfo.php causing error if no data to graph
	updated to jqueryui 1.11.4 library
	updated to highcharts 4.1.5 library
	tweaked layout size to maximize screen space e.g. thinner borders
	tweaked CSS for initial attempt at mobile device support - screen scales automatically to suit device
	improved performance of getdotstatus.php code & SQL Queries
	fixed bug in backup numbers for detailed status panel (don't flter numbers by last n)
	added popup on dotstatus for count of issues found on hover mouse over red dot
	removed SMART and SCAV panels from general distribution (personal customizations for MyTech only)
	fixed panel drag and drop: panel doesn't come to front of stack




------------------------------------------------------------------------------------
