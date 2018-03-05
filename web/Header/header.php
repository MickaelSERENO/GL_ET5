<!DOCTYPE html>
<html>
	<link rel="stylesheet" href="/scripts/bower_components/angular-notification-icons/dist/angular-notification-icons.css">

		<script type="text/javascript" src="/scripts/bower_components/angular-animate/angular-animate.js"></script>
	<script type="text/javascript" src="/scripts/bower_components/angular-notification-icons/dist/angular-notification-icons.js"></script>
	<script type="text/javascript" src="/scripts/header.js"></script>
	<body>
		<div ng-controller="headerController">
		<div id="topBanner">
			<ul class="list-inline">
				<div class="container-fluid">
					<div class="row">
						<div class="col-md-8">
							<li> <p>PoPS2017</p> </li>
						</div>
						<div class="col-md-2">
							<li class="topBannerRight"> <p><notification-icon count="countUnreadNotifs" animation="fade"><img src="/Resources/Images/enveloppe.jpg" width=100 height=50 ng-dblclick="goToNotif()"></img> </notification-icon></li>
						</div>
						<div class="col-md-2">
							<a href="/disconnect.php">
								<li class="topBannerRight"> <p> Déconnexion </p> </li>
							</a>
						</div>
					</div>
				</div>
			</ul>
			
		</div>
		
		<div id="menu">
			<div class="centerItem">
				<ul class="list-inline text-center">
					<div class="container-fluid">
						<div class="row">
							<div class="col-md-3">
								<li class="list-inline-item"> <a href="/dashboard"> <div class="menuItem"> Tableau de bord </div></a></li>
							</div>
							<div class="col-md-3">
								<li class="list-inline-item"> <a href="/listProject"> <div class="menuItem"> Projets </div></a></li>
							</div>
							<div class="col-md-3">
								<li class="list-inline-item"> <a href="/Client"> <div class="menuItem"> Clients </div></a></li>
							</div>
							<div class="col-md-3">
								<li class="list-inline-item"> <a href="/listContact"><div class="menuItem"> Contacts </div></a></li>
							</div>
						</div>
					</div>
				</ul>
			</div>
		</div>
		</div>
	</body>
</html>
