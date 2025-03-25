<?php
include("includes/connection.php");
$title = "Groups - Expense Splitter";
$page = "groups";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$groups = [];

$stmt = $conn->prepare("
    SELECT g.id, g.name, g.created_by, gm.status 
    FROM `groups` g
    JOIN `group_members` gm ON g.id = gm.group_id
    WHERE gm.user_id = ? AND gm.status = 'accepted'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $groups[] = $row;
}
$stmt->close();

$pending_groups = [];
$stmt = $conn->prepare("
    SELECT g.id, g.name FROM `groups` g
    JOIN `group_members` gm ON g.id = gm.group_id
    WHERE gm.user_id = ? AND gm.status = 'pending'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $pending_groups[] = $row;
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include("includes/head.php");?>
</head>
<body>
    <?php include("includes/nav.php");?>

    <section class="container py-5">
        <h2 class="text-center mb-5">Manage Groups</h2>

        <div class="row">
            <!-- Accepted Groups -->
            <div class="col-md-6">
				<div class="card px-3 py-3 shadow-sm" style="background-color: #2a1111; color: whitesmoke; min-height: 200px;">
					<h4>Your Groups</h4>
					<ul class="list-group">
						<?php foreach ($groups as $group): ?>
							<li class="list-group-item">
								<?php echo htmlspecialchars($group['name']); ?>
								<a href="group_details.php?group_id=<?php echo $group['id']; ?>" class="btn btn-sm btn-primary float-end">View</a>
								<?php if ($group['created_by'] != $user_id): ?>
									<a href="leave_group.php?group_id=<?php echo $group['id']; ?>" class="btn btn-sm btn-danger float-end me-2">Leave</a>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
						<?php if (empty($groups)) echo "<p class='text-muted' style='color:#fff;'>No groups yet. Create one below!</p>"; ?>
					</ul>
				</div>
            </div>
            <!-- Pending Invitations -->
            <div class="col-md-6">
				<div class="card px-3 py-3 shadow-sm sm-mt-5" style="background-color: #2a1111; color: whitesmoke; min-height: 200px;">	
					<h4>Pending Group Invitations</h4>
					<ul class="list-group">
						<?php foreach ($pending_groups as $group): ?>
							<li class="list-group-item">
								<?php echo htmlspecialchars($group['name']); ?>
								<a href="handle_group_request.php?action=accept&group_id=<?php echo $group['id']; ?>" class="btn btn-sm btn-success float-end me-2">Accept</a>
								<a href="handle_group_request.php?action=reject&group_id=<?php echo $group['id']; ?>" class="btn btn-sm btn-danger float-end">Reject</a>
							</li>
						<?php endforeach; ?>
						<?php if (empty($pending_groups)) echo "<p class='text-muted'>No pending invitations.</p>"; ?>
					</ul>
				</div>	
            </div>
        </div>

        <!-- Create Group Form -->
		<div class="row">
			<div class="col-md-6 offset-md-3">
				<div class="card p-5 shadow-sm mt-5" style="background-color: #2a1111; color: whitesmoke;">	
					<h4>Create New Group</h4>
					<form action="create_group.php" method="POST">
						<div class="mb-3">
							<label class="form-label">Group Name</label>
							<input type="text" class="form-control" name="group_name" required>
						</div>
						<div class="mb-3">
							<label class="form-label">Add Members</label>
							<input type="text" class="form-control" name="members" placeholder="Enter usernames or merchant IDs, separated by commas" required>
						</div>
						<button type="submit" class="btn btn-success">Create Group</button>
					</form>
				</div>
			</div>
		</div>	
		
    </section>

    <?php include("includes/footer.php");?>

</body>
</html>
