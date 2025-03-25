<?php
include("includes/connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['group_id'])) {
    $group_id = intval($_POST['group_id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT created_by FROM groups WHERE id = ?");
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $stmt->bind_result($created_by);
    $stmt->fetch();
    $stmt->close();

    if ($user_id == $created_by) {
        echo "<script>console.log('You are the group admin. Transfer admin rights before leaving.');</script>";
        header("Location: group_details.php?group_id=$group_id&status=admin_transfer_required");
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $group_id, $user_id);

    if ($stmt->execute()) {
        echo "<script>console.log('You have left the group.');</script>";
        header("Location: groups.php?status=left_successfully");
    } else {
        echo "<script>console.log('Error leaving group.');</script>";
        header("Location: group_details.php?group_id=$group_id&status=leave_error");
    }

    $stmt->close();
}

$conn->close();
?>
