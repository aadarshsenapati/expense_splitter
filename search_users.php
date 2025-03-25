<?php
include("includes/connection.php");
if ($conn->connect_error) die("Database error");

$query = trim($_POST["query"]);
$stmt = $conn->prepare("SELECT name FROM users WHERE name LIKE CONCAT('%', ?, '%') LIMIT 5");
$stmt->bind_param("s", $query);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    echo '<div class="suggestion list-group-item">' . htmlspecialchars($row["name"]) . '</div>';
}
$stmt->close();
$conn->close();
?>