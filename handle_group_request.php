<?php
include("includes/connection.php");
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (!isset($_GET['action'], $_GET['group_id']) || !in_array($_GET['action'], ['accept', 'reject'])) {
    echo "<script>console.log('Invalid request.');</script>";
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$group_id = intval($_GET['group_id']);
$action = $_GET['action'];

$stmt = $conn->prepare("SELECT g.created_by FROM group_members gm 
                        JOIN `groups` g ON gm.group_id = g.id 
                        WHERE gm.group_id = ? AND gm.user_id = ? AND gm.status = 'pending'");
$stmt->bind_param("ii", $group_id, $user_id);
$stmt->execute();
$stmt->bind_result($group_creator);
$request_exists = $stmt->fetch();
$stmt->close();

if (!$request_exists) {
    echo "<script>console.log('Invalid request or request already processed.');</script>";
    header("Location: dashboard.php");
    exit();
}

if ($action === 'accept') {
    $stmt = $conn->prepare("UPDATE group_members SET status = 'accepted' WHERE group_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $group_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $notifyStmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $message = "User ID $user_id has accepted the invite to your group.";
    $notifyStmt->bind_param("is", $group_creator, $message);
    $notifyStmt->execute();
    $notifyStmt->close();

    echo "<script>console.log('You have joined the group!');</script>";
} elseif ($action === 'reject') {
    $stmt = $conn->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ? AND status = 'pending'");
    $stmt->bind_param("ii", $group_id, $user_id);
    $stmt->execute();
    $stmt->close();

    echo "<script>console.log('Group invite rejected.');</script>";
}

$conn->close();
header("Location: dashboard.php");
exit();
?>