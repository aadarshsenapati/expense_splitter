<?php
include("includes/connection.php");
$title = "Create New Split - Expense Splitter";
$page = "split";
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$friends = [];
$friendQuery = "SELECT u.name FROM friends f 
                JOIN users u ON (f.user_id = u.id OR f.friend_id = u.id)
                WHERE (f.user_id = ? OR f.friend_id = ?) AND f.status = 'accepted' AND u.id != ?";
$stmt = $conn->prepare($friendQuery);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $friends[] = $row['name'];
}
$stmt->close();

$groups = [];
$groupQuery = "SELECT g.id, g.name 
               FROM `groups` g 
               JOIN `group_members` gm ON g.id = gm.group_id
               WHERE gm.user_id = ?";
$stmt = $conn->prepare($groupQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $groups[] = $row;
}
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $amount = floatval($_POST['amount']);
    $notes = trim($_POST['notes']);
    $participants = [];

    if (!empty($_POST['group_id'])) {
        $group_id = intval($_POST['group_id']);
        $stmt = $conn->prepare("SELECT u.name FROM group_members gm JOIN users u ON gm.user_id = u.id WHERE gm.group_id = ?");
        $stmt->bind_param("i", $group_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $participants[] = $row['name'];
        }
        $stmt->close();
    } else {
        $participants = explode(',', trim($_POST['participants']));
    }

    if (empty($title) || $amount <= 0 || empty($participants)) {
        echo json_encode(["status" => "error", "message" => "All fields except notes are required."]);
        exit();
    }

    foreach ($participants as $participant) {
        $participant = trim($participant);
        if (!in_array($participant, $friends) && empty($_POST['group_id'])) {
            echo json_encode(["status" => "error", "message" => "You can only split expenses with friends or groups."]);
            exit();
        }
    }

    $stmt = $conn->prepare("INSERT INTO expenses (user_id, title, amount, notes) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $user_id, $title, $amount, $notes);
    if ($stmt->execute()) {
        $expense_id = $stmt->insert_id;
        $splitAmount = round($amount / count($participants), 2);

        foreach ($participants as $username) {
            $username = trim($username);

            $userStmt = $conn->prepare("SELECT id FROM users WHERE name = ?");
            $userStmt->bind_param("s", $username);
            $userStmt->execute();
            $userStmt->bind_result($participant_id);
            if ($userStmt->fetch()) {
                $userStmt->close();

                $splitStmt = $conn->prepare("INSERT INTO splits (expense_id, user_id, amount) VALUES (?, ?, ?)");
                $splitStmt->bind_param("iid", $expense_id, $participant_id, $splitAmount);
                $splitStmt->execute();
                $splitStmt->close();

                $transactionStmt = $conn->prepare("INSERT INTO transactions (user_id, date, description, amount, status) VALUES (?, NOW(), ?, ?, 'Pending')");
                $description = "Split: " . $title;
                $negativeAmount = -$splitAmount;
                $transactionStmt->bind_param("isd", $participant_id, $description, $negativeAmount);
                $transactionStmt->execute();
                $transactionStmt->close();
            }
        }

        header("Location: dashboard.php?success=1");
		exit();
    } else {
        header("Location: new_split.php?error=1");
		exit();
    }

    $stmt->close();
    $conn->close();
    exit();
}
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
            <h1 class="display-4">Create a New Expense Split</h1>
            <p class="lead">Easily split expenses with your friends and track balances.</p>
        </div>
    </header>

    <!-- New Split Form -->
    <section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-5 shadow-sm" style="background-color: #2a1111; color: whitesmoke;">
                <h3 class="text-center mb-4">New Split Details</h3>
                <form id="splitForm" method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label">Expense Title</label>
                        <input type="text" class="form-control" name="title" id="title" placeholder="e.g., Dinner, Trip, Rent" required>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" class="form-control" name="amount" id="amount" placeholder="Enter amount" required>
                    </div>
                    
                    <!-- Group Selection -->
                    <div class="mb-3">
                        <label for="group" class="form-label">Split With</label>
                        <select class="form-select" name="group_id" id="group">
                            <option value="">-- Choose Friends or Group --</option>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?php echo $group['id']; ?>">
                                    <?php echo htmlspecialchars($group['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Friends Selection -->
                    <div class="mb-3">
                        <label for="participants" class="form-label">Or Select Friends</label>
                        <input type="text" class="form-control" name="participants" id="participants" placeholder="Enter usernames">
                        <div id="suggestions" class="list-group position-absolute"></div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="Any additional details"></textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-success btn-lg">Create Split</button>
                        <a href="dashboard.php" class="btn btn-secondary btn-lg ms-2">Cancel</a>
                    </div>
                </form>
                <div id="responseMessage" class="text-center mt-3"></div>
            </div>
        </div>
    </div>
</section>


    <!-- Footer -->
    <?php include("includes/footer.php");?>

<script>
$(document).ready(function () {
    var friends = <?php echo json_encode($friends); ?>;

    $("#participants").on("input", function () {
        var query = $(this).val().toLowerCase();
        var suggestions = friends.filter(name => name.toLowerCase().includes(query));
        
        if (suggestions.length > 0) {
            var suggestionHtml = suggestions.map(name => `<div class='suggestion list-group-item'>${name}</div>`).join('');
            $("#suggestions").html(suggestionHtml).show();
        } else {
            $("#suggestions").hide();
        }
    });

    $(document).on("click", ".suggestion", function () {
        var selectedName = $(this).text();
        var currentNames = $("#participants").val().split(',');
        if (!currentNames.includes(selectedName)) {
            currentNames.push(selectedName);
            $("#participants").val(currentNames.join(', '));
        }
        $("#suggestions").hide();
    });

    $("#splitForm").submit(function (event) {
        event.preventDefault();
        $.post("new_split.php", $(this).serialize(), function (data) {
            if (data.status === "success") {
                window.location.href = data.redirect;
            } else {
                $("#responseMessage").html('<p class="text-danger">' + data.message + '</p>');
            }
        }, "json");
    });
});
</script>

</body>
</html>
