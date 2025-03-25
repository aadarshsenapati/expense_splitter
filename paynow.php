<?php
include("includes/connection.php");
$title = "Pay Now - Expense Splitter";

include("razor-config.php");
require('razorpay-php/Razorpay.php');
use Razorpay\Api\Api;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("<div class='error'>Database error. Please try again later.</div>");
}

if($_POST['action']=='add'){
	$order_id = str_pad(rand(0, pow(10, 5)-1), 5, '0', STR_PAD_LEFT);
	$order_date	= date("Y-m-d H:i:s");
	$transaction_id = rand();
	$total_amount = $_POST['amount'];	
	$tran_id	  = $_POST['tran_id'];	
	
	$user_id = $_SESSION['user_id'];
	$stmt = $conn->prepare("SELECT name,email,mobile FROM users WHERE id = ?");
	$stmt->bind_param("i", $user_id);
	$stmt->execute();
	$stmt->bind_result($name,$email,$mobile);
	$stmt->fetch();
	$stmt->close();

	$_SESSION['transaction_id'] = $transaction_id;
	$_SESSION['tran_id'] 		= $tran_id;
	$api = new Api($keyId, $keySecret);
	$orderData = [
		'receipt'         => $order_id,
		'amount'          => $total_amount*100,
		'currency'        => 'INR',
		'payment_capture' => 1
	];
	$razorpayOrder = $api->order->create($orderData);
	$razorpayOrderId = $razorpayOrder['id'];
	$_SESSION['razorpay_order_id'] = $razorpayOrderId;

	$_SESSION['amount'] = $total_amount;
	$displayAmount = $amount = $orderData['amount'];

	$data = [
		"key"               => $keyId,
		"amount"            => $amount,
		"name"              => $name,
		"description"       => "Split Expense",
		"image"             => "",
		"prefill"           => [
								"name"              => $name,
								"email"             => $email,
								"contact"           => $mobile,
								],
		"notes"             => [
								"address"           =>'',
								"merchant_order_id" => $order_id,
								],
		"theme"             => [
		"color"             => "#8559b1"],
		"order_id"          => $razorpayOrderId,
	];

	$json = json_encode($data);

	}else{
		print "<script>";
		print "self.location='settle_up.php';";
		print "</script>";
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

    <!-- Settle Up Section -->
    <section class="container py-5" style="min-height:calc(100vh - 205px);">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4 shadow-sm">
                    <h3 class="text-center">Settle Up</h3>
                    <hr>

                    
                        <form id="settleUpForm" action="paynow.php" method="POST">
                            <!-- Select Expense Dropdown -->
                            

                            <!-- Amount Due Field -->
                            <div class="mb-3">
                                <label for="amount-due" class="form-label">Amount Due: <?php echo $total_amount; ?></label>
                                
                            </div>

                            

                            <!-- Submit Button -->
                            <button type="button" id="rzp-button1" class="btn btn-success w-100">Pay Now</button>
                        </form>
                   

                    <div class="text-center mt-3">
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include("includes/footer.php");?>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<form name='razorpayform' action="process_payment.php" method="POST">
<input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
<input type="hidden" name="razorpay_signature"  id="razorpay_signature" >
</form>
<script>
var options = <?php echo $json?>;
	options.handler = function (response){
	document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
	document.getElementById('razorpay_signature').value = response.razorpay_signature;
	document.razorpayform.submit();
};
	options.theme.image_padding = false;
	options.modal = {
	ondismiss: function() {
	console.log("This code runs when the popup is closed");
},
	escape: true,
	backdropclose: false
};
var rzp = new Razorpay(options);
	document.getElementById('rzp-button1').onclick = function(e){
	rzp.open();
	e.preventDefault();
}
</script>
</body>
</html>
