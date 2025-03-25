<?php
include("includes/connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
    $_SESSION['error'] = "Database connection failed.";
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];

    if (!isset($_POST['razorpay_payment_id'])) {
        $_SESSION['error'] = "Missing transaction details.";
        header("Location: settle_up.php");
        exit();
    }
	
    $transaction_id = $_SESSION['tran_id'];
    $amount 		= floatval($_SESSION['amount']);
    $tran_id 		= $_SESSION['tran_id'];
    $razorpay_signature = $_POST['razorpay_signature'];
    $razorpay_order_id = $_SESSION['razorpay_order_id'];

    if ($transaction_id <= 0 || $amount <= 0) {
        $_SESSION['error'] = "Invalid transaction details.";
        header("Location: settle_up.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT user_id, amount, status FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $transaction_id, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $_SESSION['error'] = "Transaction not found.";
        header("Location: settle_up.php");
        exit();
    }

    $stmt->bind_result($trans_user_id, $trans_amount, $trans_status);
    $stmt->fetch();
    $stmt->close();

    if ($trans_status === "Paid") {
        $_SESSION['error'] = "This transaction has already been settled.";
        header("Location: settle_up.php");
        exit();
    }

    $updateStmt = $conn->prepare("UPDATE transactions SET status = 'Paid', 
			`razorpay_signature`='".$razorpay_signature."',
			`razorpay_order_id` ='".$razorpay_order_id."' WHERE id = ?");
    $updateStmt->bind_param("i", $transaction_id);
    if ($updateStmt->execute()) {
        $updateStmt->close();

        $updateSplitStmt = $conn->prepare("UPDATE splits SET status = 'Paid' WHERE user_id = ? AND amount = ?");
        $updateSplitStmt->bind_param("id", $user_id, $amount);
        $updateSplitStmt->execute();
        $updateSplitStmt->close();

        $_SESSION['success'] = "Payment successful!";
        header("Location: dashboard.php?apy=1");
        exit();
    } else {
        $_SESSION['error'] = "Payment processing failed. Try again.";
        header("Location: settle_up.php");
        exit();
    }

    $conn->close();
    exit();
}
?>