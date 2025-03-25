<?php
include("includes/connection.php");
$title = "Dashboard - Expense Splitter";
$page = "notify";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name);
$stmt->fetch();
$stmt->close();

$balanceQuery = "SELECT 
    COALESCE(SUM(CASE WHEN status = 'Pending' THEN amount ELSE 0 END), 0) AS total, 
    COALESCE(SUM(CASE WHEN amount < 0 AND status = 'Pending' THEN amount ELSE 0 END), 0) AS owed_by_you,
    COALESCE(SUM(CASE WHEN amount > 0 AND status = 'Pending' THEN amount ELSE 0 END), 0) AS owed_to_you
    FROM transactions WHERE user_id = ?";

$stmt = $conn->prepare($balanceQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($balance, $owed_by_you, $owed_to_you);
$stmt->fetch();
$stmt->close();

$owedToYouQuery = "SELECT COALESCE(SUM(s.amount), 0) AS owed_to_you 
                   FROM splits s 
                   JOIN expenses e ON s.expense_id = e.id
                   WHERE e.user_id = ? AND s.status = 'Pending'";

$stmt = $conn->prepare($owedToYouQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($owed_to_you);
$stmt->fetch();
$stmt->close();

$splits = [];
$splitsQuery = "SELECT e.title, e.amount, s.amount AS split_amount, s.user_id, u.name AS participant, s.status 
                FROM splits s 
                JOIN expenses e ON s.expense_id = e.id 
                JOIN users u ON s.user_id = u.id
                WHERE e.user_id = ? OR s.user_id = ? 
                ORDER BY e.id DESC";

$stmt = $conn->prepare($splitsQuery);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $splits[] = $row;
}
$stmt->close();

$friend_requests = [];
$stmt = $conn->prepare("SELECT f.id, u.name FROM friends f 
                        JOIN users u ON f.user_id = u.id 
                        WHERE f.friend_id = ? AND f.status = 'pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $friend_requests[] = $row;
}
$stmt->close();

$group_requests = [];
$stmt = $conn->prepare("SELECT gm.id, g.name FROM `group_members` gm 
                        JOIN `groups` g ON gm.group_id = g.id 
                        WHERE gm.user_id = ? AND gm.status = 'pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $group_requests[] = $row;
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
    <!-- Navbar -->
    <?php include("includes/nav.php");?>

    <!-- Notifications -->
    <section class="container mt-4" style="min-height:calc(100vh - 230px);">
        <h4>Notifications</h4>
        <ul class="list-group">
            <?php foreach ($friend_requests as $request): ?>
                <li class="list-group-item">
                    <b><?php echo htmlspecialchars($request['name']); ?></b> sent you a friend request.
                    <a href="handle_friend_request.php?action=accept&request_id=<?php echo $request['id']; ?>" class="btn btn-sm btn-success">Accept</a>
                    <a href="handle_friend_request.php?action=reject&request_id=<?php echo $request['id']; ?>" class="btn btn-sm btn-danger">Reject</a>
                </li>
            <?php endforeach; ?>
            <?php foreach ($group_requests as $group): ?>
                <li class="list-group-item">
                    You have been invited to join <b><?php echo htmlspecialchars($group['name']); ?></b>.
                    <a href="handle_group_request.php?action=accept&group_id=<?php echo $group['id']; ?>" class="btn btn-sm btn-success">Accept</a>
                    <a href="handle_group_request.php?action=reject&group_id=<?php echo $group['id']; ?>" class="btn btn-sm btn-danger">Reject</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <!-- Footer -->
    <?php include("includes/footer.php");?>
</body>
</html>