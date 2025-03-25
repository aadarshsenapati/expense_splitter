<?php
include("includes/connection.php");
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "errors" => ["Database connection failed: " . $conn->connect_error]]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : "";
    $password = isset($_POST['password']) ? $_POST['password'] : "";

    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "errors" => ["Email and password are required."]]);
        exit();
    }

    $stmt = $conn->prepare("SELECT id, name, password_hash FROM users WHERE email = ?");
    if (!$stmt) {
        echo json_encode(["status" => "error", "errors" => ["Database error: " . $conn->error]]);
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        echo json_encode(["status" => "error", "errors" => ["Invalid email or password."]]);
        exit();
    }

    $stmt->bind_result($user_id, $name, $hashed_password);
    $stmt->fetch();

    if (!password_verify($password, $hashed_password)) {
        echo json_encode(["status" => "error", "errors" => ["Invalid email or password."]]);
        exit();
    }

    $_SESSION['user_id'] = $user_id;
    $_SESSION['name'] 	= $name;
    $_SESSION['email'] = $email;

    echo json_encode(["status" => "success"]);
}

$conn->close();
?>