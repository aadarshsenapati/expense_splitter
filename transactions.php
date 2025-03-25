<?php
include("includes/connection.php");
$title = "Transactions - Expense Splitter";
$page = "transactions";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$transactions = [];

$stmt = $conn->prepare("SELECT id, date, description, amount, status FROM transactions WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include("includes/head.php");?>
    <style>
        .transaction-container {
            height: 500px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include("includes/nav.php");?>

    <section class="container py-5"  style="min-height:calc(100vh - 205px);">
        <h2 class="text-center mb-4">Your Transactions</h2>
        
        <div class="table-responsive transaction-container">
            <table class="table table-bordered text-center">
                <thead class="table-dark text-white">
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $index => $transaction): ?>
                            <tr class="<?php echo $index % 2 === 0 ? 'bg-light' : 'bg-secondary text-white'; ?>">
                                <td><?php echo htmlspecialchars($transaction['date']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                <td class="<?php echo $transaction['amount'] < 0 ? 'text-danger' : 'text-success'; ?>">
                                    $<?php echo number_format(abs($transaction['amount']), 2); ?>
                                </td>
                                <td class="<?php echo $transaction['status'] === 'Pending' ? 'text-warning' : 'text-success'; ?>">
                                    <?php echo htmlspecialchars($transaction['status']); ?>
                                </td>
                                <td>
                                    <?php if ($transaction['status'] === 'Pending'): ?>
                                        <a href="settle_up.php?id=<?php echo $transaction['id']; ?>" class="btn btn-success btn-sm">Settle Up</a>
                                    <?php else: ?>
                                        <span>-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Footer -->
    <?php include("includes/footer.php");?>
</body>
</html>
