<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
	<div class="container">
		<a class="navbar-brand candal logo" href="index.php">Expense Splitter</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav ms-auto">
				<li class="nav-item"><a class="nav-link <?php if($page == "dashboard"){ echo 'active'; } ?>" href="dashboard.php">Dashboard</a></li>
				<li class="nav-item"><a class="nav-link <?php if($page == "transactions"){ echo 'active'; } ?>" href="transactions.php">Transactions</a></li>
				<li class="nav-item"><a class="nav-link <?php if($page == "groups"){ echo 'active'; } ?>" href="groups.php">Create Group</a></li>
				<li class="nav-item"><a class="nav-link <?php if($page == "notify"){ echo 'active'; } ?>" href="notify.php">Notifications</a></li>
				<li class="nav-item"><a class="nav-link <?php if($page == "split"){ echo 'active'; } ?>" href="new_split.php">New Split</a></li>
				<li class="nav-item"><a class="nav-link <?php if($page == "friends"){ echo 'active'; } ?>" href="friends.php">Friends</a></li>
				<li class="nav-item"><a class="nav-link" href="profile.php"><i class="bi bi-person-circle" style="font-size: 1.3rem;"></i></a></li>
				<li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right" style="font-size: 1.3rem;"></i></a></li>
				</ul>
		</div>
	</div>
</nav>