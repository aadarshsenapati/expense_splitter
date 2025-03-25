<?php
include("includes/connection.php");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "User not logged in.";
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $group_name = trim($_POST['group_name']);
    $members = explode(',', trim($_POST['members']));

    if (empty($group_name) || empty($members)) {
        $_SESSION['error_message'] = "Group name and members are required.";
        header("Location: create_group.php");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO groups (name, created_by) VALUES (?, ?)");
    $stmt->bind_param("si", $group_name, $user_id);
    if ($stmt->execute()) {
        $group_id = $stmt->insert_id;

        $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $group_id, $user_id);
        $stmt->execute();

        foreach ($members as $member) {
            $member = trim($member);
            $stmt = $conn->prepare("SELECT id FROM users WHERE name = ?");
            $stmt->bind_param("s", $member);
            $stmt->execute();
            $stmt->bind_result($member_id);
            if ($stmt->fetch()) {
                $stmt->close();
                $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $group_id, $member_id);
                $stmt->execute();
            }
        }

        $_SESSION['success_message'] = "Group created successfully!";
        header("Location: groups.php");
    } else {
        $_SESSION['error_message'] = "Error creating group.";
        header("Location: create_group.php");
    }
}

$conn->close();
?>