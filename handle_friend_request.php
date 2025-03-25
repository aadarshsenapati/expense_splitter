<?php
include("includes/connection.php");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];

    $stmt = $conn->prepare("SELECT rejection_count FROM friends WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt->bind_result($rejection_count);
    $stmt->fetch();
    $stmt->close();

    if (isset($_POST['accept'])) {
        $stmt = $conn->prepare("UPDATE friends SET status = 'accepted' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();
        echo "<script>console.log('Friend request accepted.');</script>";

    } elseif (isset($_POST['reject'])) {
        if ($rejection_count >= 2) {
            $stmt = $conn->prepare("UPDATE friends SET status = 'blocked' WHERE id = ?");
            echo "<script>console.log('Friend request rejected and user is now blocked.');</script>";
        } else {
            $stmt = $conn->prepare("UPDATE friends SET status = 'rejected', rejection_count = rejection_count + 1 WHERE id = ?");
            echo "<script>console.log('Friend request rejected.');</script>";
        }
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
header("Location: friends.php");
exit();
?>