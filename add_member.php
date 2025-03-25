<?php
include("includes/connection.php");

header("Content-Type: text/html");

if (!isset($_SESSION['user_id'])) {
    echo "<script>console.log('Error: You must be logged in.');</script>";
    echo "<p style='color: red;'>You must be logged in.</p>";
    exit();
}


if ($conn->connect_error) {
    echo "<script>console.log('Error: Database connection failed.');</script>";
    echo "<p style='color: red;'>Database connection failed.</p>";
    exit();
}

$user_id = $_SESSION['user_id'];
$group_id = intval($_POST['group_id']);
$member_identifier = trim($_POST['member_identifier']);

if (empty($member_identifier) || empty($group_id)) {
    echo "<script>console.log('Error: Invalid input.');</script>";
    echo "<p style='color: red;'>Invalid input.</p>";
    exit();
}

if (preg_match('/^\d{6}$/', $member_identifier)) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE merchant_number = ?");
} else {
    $stmt = $conn->prepare("SELECT id FROM users WHERE name = ?");
}

$stmt->bind_param("s", $member_identifier);
$stmt->execute();
$stmt->bind_result($member_id);
$user_exists = $stmt->fetch();
$stmt->close();

if (!$user_exists) {
    echo "<script>console.log('Error: User not found.');</script>";
    echo "<p style='color: red;'>User not found.</p>";
    exit();
}

$stmt = $conn->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
$stmt->bind_param("ii", $group_id, $member_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo "<script>console.log('Error: User is already in the group.');</script>";
    echo "<p style='color: red;'>User is already in the group.</p>";
    exit();
}
$stmt->close();

$stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id, status) VALUES (?, ?, 'pending')");
$stmt->bind_param("ii", $group_id, $member_id);
$stmt->execute();
$stmt->close();

echo "<script>console.log('Success: Member request sent!');</script>";
echo "<p style='color: green;'>Member request sent!</p>";

$conn->close();
exit();
?>