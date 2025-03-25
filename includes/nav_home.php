<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
	<div class="container">
		<a class="navbar-brand candal logo" href="index.php">Expense Splitter</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav ms-auto">
				<li class="nav-item"><a class="nav-link" href="index.php#features">Features</a></li>
				<li class="nav-item"><a class="nav-link" href="index.php#how-it-works">How It Works</a></li>
				<li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
				<?php if(isset($_SESSION['user_id'])){ ?>
				<li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-person-circle" style="font-size: 1.3rem;"></i></a></li>
				<li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right" style="font-size: 1.3rem;"></i></a></li>
				<?php }else{ ?>
				<li class="nav-item"><a class="btn btn-primary btn-sm" style="margin-top: 4px;" href="login.php">Login</a></li>
				<li class="nav-item"><a class="btn btn-primary btn-sm" style="margin-top: 4px;" href="signup.php">Sign Up</a></li>
				<?php } ?>
			</ul>
		</div>
	</div>
</nav>