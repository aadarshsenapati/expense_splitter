<?php
include("includes/connection.php");

if (!isset($_SESSION['user_id'])) {
    //echo "<script>console.log('Error: You must be logged in. Redirecting...');</script>";
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
  //  echo "<script>console.log('Error: Database connection failed.');</script>";
    //echo "<p style='color: red;'>Database connection failed: " . $conn->connect_error . "</p>";
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $group_name = trim($_POST['group_name']);
    $members = isset($_POST['members']) ? (is_array($_POST['members']) ? $_POST['members'] : explode(',', $_POST['members'])) : [];

    if (empty($group_name) || empty($members)) {
        echo "<script>console.log('Error: Group name and members are required.');</script>";
        echo "<p style='color: red;'>Group name and members are required.</p>";
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO `groups` (name, created_by) VALUES (?, ?)");
    $stmt->bind_param("si", $group_name, $user_id);

    if ($stmt->execute()) {
        $group_id = $stmt->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO `group_members` (group_id, user_id, status) VALUES (?, ?, 'accepted')");
        $stmt->bind_param("ii", $group_id, $user_id);
        $stmt->execute();
        $stmt->close();

        foreach ($members as $member) {
            $member = trim($member);
            
            if (preg_match('/^\d{6}$/', $member)) {
                $stmt = $conn->prepare("SELECT id FROM users WHERE merchant_number = ?");
            } else {
                $stmt = $conn->prepare("SELECT id FROM users WHERE name = ?");
            }

            $stmt->bind_param("s", $member);
            $stmt->execute();
            $stmt->bind_result($member_id);
            if ($stmt->fetch()) {
                $stmt->close();

                $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id, status) VALUES (?, ?, 'pending')");
                $stmt->bind_param("ii", $group_id, $member_id);
                $stmt->execute();
                $stmt->close();

                $notification = "You have been invited to join the group '$group_name'.";
                $notifyStmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $notifyStmt->bind_param("is", $member_id, $notification);
                $notifyStmt->execute();
                $notifyStmt->close();
            }
        }

        //echo "<script>console.log('Success: Group \"$group_name\" created successfully!');</script>";
        //echo "<p style='color: green;'>Group '$group_name' created successfully!</p>";
		header("Location: groups.php?success=1");
		exit();
    } else {
        //echo "<script>console.log('Error: Failed to create group.');</script>";
        //echo "<p style='color: red;'>Error creating group.</p>";
		header("Location: groups.php?error=1");
		exit();
    }

    $conn->close();
    exit();
}
?>