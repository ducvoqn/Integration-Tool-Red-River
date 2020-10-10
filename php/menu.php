<?php
	session_start();
?>

<?php   
    include_once "php/includes/dbh.inc.php";
?>
<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="navbar-header">
		<a class="navbar-brand" href="./index.php"><i class="fa fa-home fa-fw"></i> Home</a>
	</div>

	<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
		<span class="sr-only">Toggle navigation</span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
	</button>




	<!-- Top Navigation: Left Menu -->
	<!--
	<ul class="nav navbar-nav navbar-left navbar-top-links">
		<li><a href="./importDataFromExcelFile.php"> Import Data</a></li>
	</ul>
	-->

	<!-- Top Navigation: Right Menu -->
	<ul class="nav navbar-right navbar-top-links">
		<?php
			if (isset($_SESSION['userId'])) {
				echo '<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#">
					<i class="fa fa-user fa-fw"></i>'.$_SESSION['name'].'
				</a>
				</li>
				<li>
					<form action="php/includes/logout.inc.php" method="post">
						<button>Logout</button>
					</form>
				</li>';
			}

			

			else {
				echo '<li>
				<ul class="nav navbar-inverse">
						<li>
							<a href="php/login.php">Login</a>
									
						</li>	
					</ul>
			</li>';
			}
		?>
	</ul>

	<div class="navbar-default sidebar" role="navigation">
		<div class="sidebar-nav navbar-collapse">

			<ul class="nav in" id="side-menu">
				<li>
					<a href="./index.php"><i class="fa fa-dashboard"></i> Dashboard </a>
				</li>
		<?php
			if (isset($_SESSION['userId'])) {
				echo '<li>
					<a href="./account.php"><i class="fa fa-users"></i> Users</a>
				</li>';
				
			}
		?>
				<li>
					<a href="./importDataFromExcelFile.php"><i class="fa fa-upload"></i> Import</a>
				</li>
				<li>
					<a href="#"><i class="fa fa-download "></i> Export<span class="fa arrow"></span></a>
					<ul class="nav nav-second-level">
					    <li>
                            <a href="./exportAllTable.php">Export all tables</a>
                        </li>
					    <li>
                            <a href="./exportATable.php">Export a table</a>
                        </li>
                        <li>
                            <a href="./exportTable.php">Export a property</a>
                        </li>
					</ul>
				</li>
				<li>
					<a href="./search.php"><i class="fa fa-search"></i> Search </a>
				</li>
				<li>
					<a href="./viewData.php"><i class="fa fa-th"></i> Show all</span></a>
				</li>

			</ul>

		</div>
	</div>
</nav>