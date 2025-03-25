<?php
include("includes/connection.php");
$title="Group Details";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['group_id']) || !is_numeric($_GET['group_id'])) {
    header("Location: dashboard.php");
    exit();
}

$group_id = intval($_GET['group_id']);

$stmt = $conn->prepare("SELECT name, created_by FROM `groups` WHERE id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$stmt->bind_result($group_name, $created_by);
$stmt->fetch();
$stmt->close();

$members = [];
$stmt = $conn->prepare("SELECT u.id, u.name, gm.status FROM group_members gm 
                        JOIN users u ON gm.user_id = u.id 
                        WHERE gm.group_id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include("includes/head.php");?>
</head>
<body>
<?php include("includes/nav.php");?>

<section class="container py-5" style="min-height:calc(100vh - 200px);">
    <h2 class="text-center mb-4"><?php echo htmlspecialchars($group_name); ?></h2>

    <div class="row">
        <div class="col-md-6">
            <h4>Group Members</h4>
            <ul class="list-group">
                <?php if (!empty($members)): ?>
                    <?php foreach ($members as $member): ?>
                        <li class="list-group-item">
                            <?php echo htmlspecialchars($member['name']); ?>
                            <?php if ($member['status'] == 'pending'): ?>
                                <span class="badge bg-warning">Pending</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-muted">No members yet.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Add Member Form -->
        <div class="col-md-6">
            <h4>Add Member</h4>
            <form id="addMemberForm">
                <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                <div class="mb-3">
                    <label class="form-label">Merchant ID or Username</label>
                    <input type="text" class="form-control" name="member_identifier" id="member_identifier" required>
                </div>
                <button type="submit" class="btn btn-success">Add Member</button>
                <div id="addMemberResponse" class="mt-2 text-center"></div>
            </form>
        </div>
    </div>
</section>

<?php include("includes/footer.php");?>

<script>
    $(document).ready(function () {
        $("#addMemberForm").submit(function (event) {
            event.preventDefault();
            $.ajax({
                url: "add_member.php",
                type: "POST",
                data: $(this).serialize(),
                dataType: "json",
                success: function (data) {
                    console.log("Response received:", data);
                    if (data.status === "success") {
                        $("#addMemberResponse").html('<p class="text-success">' + data.message + '</p>');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        $("#addMemberResponse").html('<p class="text-danger">' + data.message + '</p>');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    $("#addMemberResponse").html('<p class="text-danger">An error occurred. Please try again.</p>');
                }
            });
        });
    });
</script>

</body>
</html>
