<?php
	require_once __DIR__.'/../../PSQL/NotifRqst.php';
	session_start();

	// Redirect if not signed in
	if(!isset($_SESSION["email"]))
	{
		header('Location: /connection.php');
	}

//	$_SESSION["email"] = "jean.dupont@email.com";
	$notifRequest = new NotifRqst();
	$listeNotifs = $notifRequest->getNotifs($_SESSION["email"],false);
	
?>
  <!DOCTYPE html>
  <html>

  <head>
    <meta charset="UTF-8">
    <title>Projet GL</title>

    <script type="text/javascript" src="/scripts/bower_components/xmlhttprequest/XMLHttpRequest.js"></script>
    <script type="text/javascript" src="/scripts/bower_components/angular/angular.js"></script>
    <script type="text/javascript" src="/scripts/bower_components/angular-animate/angular-animate.js"></script>
    <script type="text/javascript" src="/scripts/bower_components/angular-sanitize/angular-sanitize.js"></script>
    <script type="text/javascript" src="/scripts/bower_components/angular-bootstrap/ui-bootstrap.js"></script>
    <script type="text/javascript" src="/scripts/connection.js"></script>
    <link rel="stylesheet" type="text/css" href="/scripts/bower_components/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="/CSS/style.css">
	<script type="text/javascript" src="/scripts/notif.js"></script>	
	<script type="text/javascript">
	var listNotifJS = JSON.parse('<?php include('..//AJAX/fetchNotifs.php');?>');
	var notifID = null;
    </script>
<?php if(isset($_GET["notifId"])): ?>
	<script type="text/javascript">
		notifID = <?= $_GET["notifId"] ?>; 
	</script>
<?php endif ?>
  </head>

  <body ng-app="myApp">
	<header class="headerConnected">
			<?php include('../Header/header.php'); ?>
	</header>
	
    <div ng-controller="formController">
      <div id="centralPart">
        <div class="alignElem">
            <div class="container-fluid">
                <div class="row topSpace">
                    <div class="col-sm-4">
                      <table class="table tableList">
		                <thead>
		                    <tr>
		                        <td>Date</td>
		                        <td>Notification</td>
		                    </tr>
		                </thead>
		                <tbody>
		                    <tr ng-repeat="notif in listNotifJS" ng-class="{unread : !notif.read}" ng-click="openNotif(notif)">
                                <td>
			                        {{ notif.theDate | date:'dd/MM/yyyy HH:mm'}}
                                </td>
                                <td>
                                    {{ notif.title }}
                                </td>
                            </tr>
                            </tbody>
                    </table>
                </div>
	            <div class="col-sm-8">
	
            	<table class="table tableContenuNotif ">
        		    <tbody>
    			      <tr>
						<td class="title" colspan=3><h2>{{ openedNotif.title }}</h2></td>
    			      </tr>
    			      <tr>
        				<td ng-if="openedNotif">{{ "Expéditeur : " +  openedNotif.senderFirstName + " " + openedNotif.senderLastName }}</td>
        				<td ng-if="openedNotif">{{ "Reçu le " +  (openedNotif.theDate | date:'dd/MM/yyyy')}}</td>
        				<td ng-if="openedNotif">Projet : <a href="/Project/infoProject.php?projectID={{openedNotif.projectID}}">{{ openedNotif.projectName }}</a></td>
    			      </tr>			
           			<tr class="table bordering">
			    	 <td class="message" colspan=3><p ng-bind-html="openedNotif.message"></p></td>
			        </tr>
				   </tbody>	
                </table>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</body>
</html>
