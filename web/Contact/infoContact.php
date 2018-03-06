<?php
	require_once __DIR__.'/../../PSQL/ContactsRqst.php';
	require_once __DIR__.'/../../Libraries/check.php';

	session_start();

	//Redirect if not signed in
	if(!isset($_SESSION["email"]))
	{
		header('Location: /connection.php');
		return;
	}

	$rank = $_SESSION['rank'];
	$contactRqst = new ContactsRqst();
	$contact = $contactRqst->getContact($_GET['contactID']);
?>

<!DOCTYPE html>
<html>
	<head>
	
		<meta charset="UTF-8">
		<title>Projet GL</title>

		<script type="text/javascript">
			var rank              = <?= $_SESSION["rank"] ?>;
			var contact           = JSON.parse('<?= json_encode($contact) ?>');
			var contactEmail      = '<?= $_GET['contactID'] ?>';
		</script>

		<script type="text/javascript" src="/scripts/bower_components/xmlhttprequest/XMLHttpRequest.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular/angular.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-animate/angular-animate.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-sanitize/angular-sanitize.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-bootstrap/ui-bootstrap.js"></script>
		<script type="text/javascript" src="/scripts/bower_components/angular-bootstrap/ui-bootstrap-tpls.js"></script>

		<script type="text/javascript" src="/scripts/infoContact.js"></script>
		<script type="text/javascript" src="/scripts/confirmModal.js"></script>
		<script type="text/javascript" src="/scripts/projectModal.js"></script>

		<link rel="stylesheet" type="text/css" href="../scripts/bower_components/bootstrap/dist/css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="../CSS/style.css">
		<link rel="stylesheet" type="text/css" href="../CSS/ganttStyle.css">
		<link rel="stylesheet" type="text/css" href="../CSS/styleContact.css">
		
	</head>

	<body ng-app="myApp">
		<header class="headerConnected">
			<?php include('../Header/header.php'); ?>
		</header>

		<?php include('../../Libraries/confirmModal.php'); ?>
		<?php include('../../Libraries/ProjectModal.php'); ?>
		
		<div ng-controller="ContactCtrl" id="centralPart">
			<div class="infoContact">
				<h3 ng-show="!inModifyStats"> {{name}} {{surname}} <span ng-show="rank != 2">({{active ? 'Actif' : 'Inactif'}})</span></h3>
				
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-12">
							<div class="smallTopSpace" ng-show="inModifyStats">
								<div class="col-md-12">
									<div class="flexDiv">
										<div> Prénom: </div>
										<div> <input type="text" ng-model="name"></input></div>
									</div>
								</div>
							</div>
							<div class="row smallTopSpace" ng-show="inModifyStats">
								<div class="col-md-12">
									<div class="flexDiv">
										<div> Nom: </div>
										<div> <input type="text" ng-model="surname"></input></div>
									</div>
								</div>
							</div>
							<div class="row smallTopSpace">
								<div class="col-md-12">
									<div class="flexDiv">
										<div> Adresse email : &nbsp;</div>
										<div ng-show="!inModifyStats">{{email}}</div>
										<div> <input type="text" ng-model="email" ng-show="inModifyStats" ng-disabled="!inModifyStats"></input></div>
									</div>
								</div>
							</div>
							<div class="row smallTopSpace">
								<div class="col-md-12">
									<div class="flexDiv">
										<div> Téléphone : &nbsp;</div>
										<div> <input type="text" ng-model="telephone" ng-disabled="!inModifyStats"></input></div>
									</div>
								</div>
							</div>
							<div class="row smallTopSpace" ng-show="rank==2">
								<div class="col-md-12">
									<div class="flexDiv">
										<div> Société : &nbsp;</div>
										<div> {{entreprise}} </div>
										<img ng-click="openClient()" class="settingImg" src="/CSS/img/settings.svg" alt="Modifier client" ng-show="inModifyStats">
									</div>
								</div>
							</div>
							<div class="row smallTopSpace">
								<div class="col-md-12">
									<div class="flexDiv">
										<div> Statut : &nbsp;</div>
										<div class="btn-group sortList" uib-dropdown dropdown-append-to-body ng-show="rank != 2">
											<button type="button" class="btn btn-primary" uib-dropdown-toggle ng-disabled="!inModifyStats || contact.rank==1">
												{{listStatus[rank]}}<span class="caret sortList"></span>
											</button>
											<ul class="dropdown-menu" uib-dropdown-menu role="menu" aria-labelledby="btn-append-to-body">
												<li role="menuitem" ng-click="changeRank(0)"><a href="">Collaborateur</a></li>
												<li role="menuitem" ng-click="changeRank(1)"><a href="">Responsable de projet</a></li>
											</ul>
										</div>
										<div ng-show="rank == 2">{{status}}</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="buttonInfo" ng-show="userRank == 2">
				<div class="centerItem">
					<ul class="list-inline text-center">
						<div class="container-fluid">
							<div class="row errorMsg" ng-show="errorMsg != ''"><div class="col-md-12">{{errorMsg}}</div></div>
							<div class="row">
								<div ng-class="{'col-md-5' : isContact}" class="col-md-3"></div>
								<div class="col-md-2" ng-show="rank != 2">
									<li class="list-inline-item"> <div class="buttonItem" ng-click="reinitPwd()"> Réinitialiser le mot de passe </div></li>
								</div>
								<div class="col-md-2">
									<li class="list-inline-item"> <div class="buttonItem" ng-click="modify()"> {{modifyText}} </div></li>
								</div>
								<div class="col-md-2" ng-show="!inModifyStats && active && rank != 2">
									<li class="list-inline-item"> <div class="buttonItem" ng-click="setInactive()"> Rendre inactif </div></li>
								</div>
								<div class="col-md-2" ng-show="!inModifyStats && !active && rank != 2">
									<li class="list-inline-item"> <div class="buttonItem" ng-click="setActive()"> Rendre actif </div></li>
								</div>
								<div class="col-md-2" ng-show="inModifyStats">
									<li class="list-inline-item"> <div class="buttonItem" ng-click="cancel()"> Annuler </div></li>
								</div>
								<div ng-class="{'col-md-5' : isContact}" class="col-md-3"></div>
							</div>
						</div>
					</ul>
				</div>
			</div>
		</div>


	</body>
</html>
