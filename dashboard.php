<?php
include("includes/connection.php");
$title = "Dashboard - Expense Splitter";
$page="dashboard";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name);
$stmt->fetch();
$stmt->close();

$balanceQuery = "SELECT 
    COALESCE(SUM(CASE WHEN status = 'Pending' THEN amount ELSE 0 END), 0) AS total, 
    COALESCE(SUM(CASE WHEN amount < 0 AND status = 'Pending' THEN amount ELSE 0 END), 0) AS owed_by_you,
    COALESCE(SUM(CASE WHEN amount > 0 AND status = 'Pending' THEN amount ELSE 0 END), 0) AS owed_to_you
    FROM transactions WHERE user_id = ?";

$stmt = $conn->prepare($balanceQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($balance, $owed_by_you, $owed_to_you);
$stmt->fetch();
$stmt->close();

$owedToYouQuery = "SELECT COALESCE(SUM(s.amount), 0) AS owed_to_you 
                   FROM splits s 
                   JOIN expenses e ON s.expense_id = e.id
                   WHERE e.user_id = ? AND s.status = 'Pending'";

$stmt = $conn->prepare($owedToYouQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($owed_to_you);
$stmt->fetch();
$stmt->close();

$splits = [];
$splitsQuery = "SELECT e.title, e.amount, s.amount AS split_amount, s.user_id, u.name AS participant, s.status 
                FROM splits s 
                JOIN expenses e ON s.expense_id = e.id 
                JOIN users u ON s.user_id = u.id
                WHERE e.user_id = ? OR s.user_id = ? 
                ORDER BY e.id DESC";

$stmt = $conn->prepare($splitsQuery);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $splits[] = $row;
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

    <!-- Hero Section -->
    <header class="bg-light text-center py-5 back">
        <div class="container">
            <h1 class="display-4">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <p class="lead">Manage and track your expenses effortlessly.</p>
        </div>
    </header>

    <!-- Balance Summary -->
    <section class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card p-4 shadow-sm text-center" style="background-color: #2a1111; color: whitesmoke; padding-top: 50px !important; padding-bottom: 50px !important;">
                    <h3>Your Total Balance</h3>
                    <h2 class="fw-bold text-success mt-2">$<?php echo number_format($balance, 2); ?></h2>
                    <p class="mb-2">Owed by You: <span class="text-danger">$<?php echo number_format(abs($owed_by_you), 2); ?></span></p>
                    <p>Owed to You: <span class="text-success">$<?php echo number_format($owed_to_you, 2); ?></span></p>
                    <div class="mt-3">
                        <a href="new_split.php" class="btn btn-success">+ New Split</a>
                        <a href="transactions.php" class="btn btn-light ms-2">View Transactions</a>
                        <a href="settle_up.php" class="btn btn-warning ms-2">Settle Up</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Splits -->
    <section class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h3 class="text-center mb-4">Your Splits</h3>
                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>Expense</th>
                                <th>Total Amount</th>
                                <th>Your Share</th>
                                <th>Participant</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($splits)): ?>
                                <?php foreach ($splits as $split): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($split['title']); ?></td>
                                        <td>$<?php echo number_format($split['amount'], 2); ?></td>
                                        <td class="text-danger">$<?php echo number_format($split['split_amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($split['participant']); ?></td>
                                        <td><?php echo htmlspecialchars($split['status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No splits found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    
    <?php include("includes/footer.php");?>
</body>
</html>
