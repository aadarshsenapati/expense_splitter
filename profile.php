<?php
include("includes/connection.php");
$title = "Profile - Expense Splitter";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

$stmt = $conn->prepare("SELECT name, email, upi, merchant_number FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $upi, $merchant_number);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = trim($_POST['name']);
    $new_upi = trim($_POST['upi']);

    if (!empty($new_name) && !empty($new_upi)) {
        $updateStmt = $conn->prepare("UPDATE users SET name = ?, upi = ? WHERE id = ?");
        $updateStmt->bind_param("ssi", $new_name, $new_upi, $user_id);
        
        if ($updateStmt->execute()) {
            $message = "Profile updated successfully.";
            $name = $new_name;
            $upi = $new_upi;
        } else {
            error_log("Error updating profile: " . $updateStmt->error);
        }
        $updateStmt->close();
    } else {
        $message = "Name and UPI ID cannot be empty.";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include("includes/head.php");?> 
    <script>
        function copyMerchantNumber() {
            let merchantNumberInput = document.getElementById("merchantNumber");
            navigator.clipboard.writeText(merchantNumberInput.value).then(() => {
                console.log("Merchant number copied to clipboard.");
            }).catch(err => console.error("Failed to copy: ", err));
        }
    </script>
</head>
<body>
    <?php include("includes/nav.php");?>

    <section class="container py-5"   style="min-height:calc(100vh - 200px);">
        <h2 class="text-center mb-4">Your Profile</h2>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-5 shadow-sm" style="background-color: #2a1111; color: whitesmoke; min-height: 200px;">
                    <p class="text-success"><?php echo $message; ?></p>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">UPI ID</label>
                            <input type="text" class="form-control" name="upi" value="<?php echo htmlspecialchars($upi); ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Merchant Number</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="merchantNumber" value="<?php echo htmlspecialchars($merchant_number); ?>" readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="copyMerchantNumber()">Copy</button>
                            </div>
                        </div>
                        <div class="mb-3 text-center"><button type="submit" class="btn btn-success btc btn-lg">Update Profile</button></div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include("includes/footer.php");?>
</body>
</html>