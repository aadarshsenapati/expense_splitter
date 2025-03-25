<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");

$host = "localhost";
$username = "root";
$password = "";
$dbname = "expense_splitter";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "errors" => ["Database connection failed."]]);
    exit();
}

function generateMerchantNumber($conn) {
    do {
        $merchant_number = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        $stmt = $conn->prepare("SELECT id FROM users WHERE merchant_number = ?");
        $stmt->bind_param("s", $merchant_number);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);
    $stmt->close();
    return $merchant_number;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = isset($_POST['name']) ? trim($_POST['name']) : "";
    $email = isset($_POST['email']) ? trim($_POST['email']) : "";
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : "";
    $upi_id = isset($_POST['upi']) ? trim($_POST['upi']) : "";
    $password = $_POST['password'] ?? "";
    $confirm_password = $_POST['confirmPassword'] ?? "";

    $errors = [];

    if (strlen($full_name) < 3) $errors[] = "Full name must be at least 3 characters long.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (!preg_match('/^\d{10}$/', $mobile)) $errors[] = "Mobile number must be 10 digits.";
    if (!preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9]+$/', $upi_id)) $errors[] = "Invalid UPI ID format.";
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[@$!%*?&]/', $password)) {
        $errors[] = "Password must be at least 8 characters long, contain an uppercase letter, a number, and a special character.";
    }
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    if (!empty($errors)) {
        echo json_encode(["status" => "error", "errors" => $errors]);
        exit();
    }

    $merchant_number = generateMerchantNumber($conn);
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, mobile, upi, password_hash, merchant_number) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $full_name, $email, $mobile, $upi_id, $hashed_password, $merchant_number);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Account created successfully."]);
    } else {
        echo json_encode(["status" => "error", "errors" => ["Error creating account."]]);
    }

    $stmt->close();
}

$conn->close();
?>
