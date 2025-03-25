<?php
include("includes/connection.php");
$title = "Settle Up - Expense Splitter";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("<div class='error'>Database error. Please try again later.</div>");
}

$user_id = $_SESSION['user_id'];
$expenses = [];

$stmt = $conn->prepare("SELECT t.id, e.title, ABS(t.amount) AS amount 
                        FROM transactions t
                        JOIN splits s ON t.id = s.id 
                        JOIN expenses e ON s.expense_id = e.id 
                        WHERE t.user_id = ? AND t.status = 'Pending'");

if (!$stmt) {
    error_log("Query preparation failed: " . $conn->error);
    die("<div class='error'>An error occurred. Please try again later.</div>");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $expenses[] = $row;
}

$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include("includes/head.php");?>
</head>
<body>
    <!-- Navbar -->
    <?php include("includes/nav.php");?>

    <!-- Settle Up Section -->
    <section class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4 shadow-sm">
                    <h3 class="text-center">Settle Up</h3>
                    <hr>

                    <?php if (empty($expenses)): ?>
                        <p class="text-center text-danger">No pending expenses to settle.</p>
                    <?php else: ?>
                        <form id="settleUpForm" action="paynow.php" method="POST">
						<input type="hidden" name="action" value="add">
                            <!-- Select Expense Dropdown -->
                            <div class="mb-3">
                                <label for="expense-title" class="form-label">Select Expense to Settle</label>
                                <select class="form-select" id="expense-title" name="tran_id" required>
                                    <option value="">-- Select an Expense --</option>
                                    <?php foreach ($expenses as $expense): ?>
                                        <option value="<?php echo $expense['id']; ?>" data-amount="<?php echo $expense['amount']; ?>">
                                            <?php echo htmlspecialchars($expense['title']); ?> - $<?php echo number_format($expense['amount'], 2); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Hidden Field for Transaction ID -->
                            <input type="hidden" name="transaction_id_hidden" id="hidden-transaction-id">

                            <!-- Amount Due Field -->
                            <div class="mb-3">
                                <label for="amount-due" class="form-label">Amount Due</label>
                                <input type="text" class="form-control" id="amount-due" name="amount" readonly>
                            </div>

                            <!-- Payment Method Selection -->
                            <div class="mb-3">
                                <label for="payment-method" class="form-label">Select Payment Method</label>
                                <select class="form-select" id="payment-method" name="payment_method" required>
                                    <option value="upi">UPI</option>
                                    <option value="card">Credit/Debit Card</option>
                                    <option value="net_banking">Net Banking</option>
                                </select>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" id="rzp-button1" class="btn btn-success w-100">Pay Now</button>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-3">
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include("includes/footer.php");?>
    <script>
    document.getElementById("expense-title").addEventListener("change", function () {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById("amount-due").value = selectedOption.dataset.amount || "";
        document.getElementById("hidden-transaction-id").value = selectedOption.value;
    });
    </script>
</body>
</html>
