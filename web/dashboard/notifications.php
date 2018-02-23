<?php
	require_once __DIR__.'/../../PSQL/NotifRqst.php';
	session_start();
/*
	// Redirect if not signed in
	if(!isset($_SESSION["email"]))
	{
		header('Location: /connection.php');
	}
*/
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
	var listNotifJS = JSON.parse(<?php echo '\''.json_encode($listeNotifs, JSON_HEX_APOS).'\'' ?>); 
	console.log(listNotifJS);
    </script>
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
			<td>Etat</td>
		      </tr>
		</thead>
		<tbody>
<?php foreach($listeNotifs as $i => $notif)
{?>
		<?php echo '<tr ng-click="selectedNotif=' . $i . '">'?>
                  <td>
			<?php echo $notif->theDate;?>
                  </td>
                  <td  <?php if(!$notif->read){echo 'class="unread"';} ?>>
                    <?php echo $notif->title;?>
                  </td>
                  <td>
                    <?php echo $notif->read;?>
                  </td>

                  </tr>
                  <?php	
						      	}
						      ?>
            </tbody>
          </table>
          </div>
	<div class="col-sm-8">
	
	<table class="table tableContenuNotif ">
		    <tbody>
			      <tr>
				<td><b>{{ listNotifJS[selectedNotif].title }}</b></td>
			      </tr>
			      <tr>
				<td>{{ listNotifJS[selectedNotif].theDate }}</td>
			      </tr>
			      <tr>
				<td>{{ listNotifJS[selectedNotif].send }}</td>
			      </tr>
			      <tr>
				<td>{{ listNotifJS[selectedNotif].projectName }}</td>
			      </tr>
			
				<tr class="table bordering">
				 <td><p>{{ listNotifJS[selectedNotif].message }}</p></td>
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

