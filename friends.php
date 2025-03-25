<?php
include("includes/connection.php");
$title = "Manage Friends";
$page = "friends";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_friend'])) {
    $friend_input = trim($_POST['friend_input']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE merchant_number = ? OR email = ?");
    $stmt->bind_param("ss", $friend_input, $friend_input);
    $stmt->execute();
    $stmt->bind_result($friend_id);
    $stmt->fetch();
    $stmt->close();

    if (!$friend_id) {
        $message = "<div class='alert alert-danger'>User not found!</div>";
    } else {
        $checkStmt = $conn->prepare("SELECT status FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
        $checkStmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
        $checkStmt->execute();
        $checkStmt->bind_result($status);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($status) {
            $message = "<div class='alert alert-warning'>Friend request already exists.</div>";
        } else {
            $insertStmt = $conn->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
            $insertStmt->bind_param("ii", $user_id, $friend_id);
            if ($insertStmt->execute()) {
                $message = "<div class='alert alert-success'>Friend request sent!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error sending request.</div>";
            }
            $insertStmt->close();
        }
    }
}

$requests = [];
$requestQuery = "SELECT f.id, u.name, u.merchant_number 
                 FROM friends f 
                 JOIN users u ON f.user_id = u.id 
                 WHERE f.friend_id = ? AND f.status = 'pending'";

$stmt = $conn->prepare($requestQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}
$stmt->close();

$friends = [];
$friendQuery = "SELECT u.name, u.merchant_number FROM friends f 
                JOIN users u ON f.friend_id = u.id 
                WHERE f.user_id = ? AND f.status = 'accepted'
                UNION
                SELECT u.name, u.merchant_number FROM friends f 
                JOIN users u ON f.user_id = u.id 
                WHERE f.friend_id = ? AND f.status = 'accepted'";

$stmt = $conn->prepare($friendQuery);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $friends[] = $row;
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
   
    <section class="container py-5" style="min-height:calc(100vh - 205px);">
		<div class="row">
			<div class="col-md-6 offset-md-3 loginf" style="box-shadow:none !important;">
				<h2 class="text-center mb-4">Manage Friends</h2>
				<?php echo $message; ?>
				<form method="post">
					<div class="mb-3">
						<label class="form-label">Add Friend (Merchant ID or Email)</label>
						<input type="text" class="form-control" name="friend_input" required>
					</div>
					<div class="mb-3"><button type="submit" name="add_friend" class="btn btn-primary btn-lg">Send Request</button></div>
				</form>

				<h3 class="mt-5">Pending Requests</h3>
				<ul class="list-group">
					<?php foreach ($requests as $request): ?>
						<li class="list-group-item d-flex justify-content-between align-items-center">
							<?php echo htmlspecialchars($request['name']); ?> (Merchant ID: <?php echo $request['merchant_number']; ?>)
							<div>
								<form method="post" action="handle_friend_request.php" style="display:inline;">
									<input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
									<button type="submit" name="accept" class="btn btn-success btn-sm">Accept</button>
									<button type="submit" name="reject" class="btn btn-danger btn-sm">Reject</button>
								</form>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>

				<h3 class="mt-5">Your Friends</h3>
				<ul class="list-group">
					<?php foreach ($friends as $friend): ?>
						<li class="list-group-item"><?php echo htmlspecialchars($friend['name']); ?> (Merchant ID: <?php echo $friend['merchant_number']; ?>)</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>        
    </section>
    
    <?php include("includes/footer.php");?>
    
</body>
</html>