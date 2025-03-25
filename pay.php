<?php
include("includes/connection.php");
require('razorpay-php/Razorpay.php');

use Razorpay\Api\Api;

$keyId = 'rzp_test_otlh8nyldgAeh9';
$keySecret = 'QTFk7asSuB90w8hmKqSqZfYy';
$displayCurrency = 'INR';

$order_id = str_pad(rand(0, pow(10, 5)-1), 5, '0', STR_PAD_LEFT);

$first_name	= $connect->real_escape_string($_POST['first_name']);
$last_name	= $connect->real_escape_string($_POST['last_name']);
$address_1	= $connect->real_escape_string($_POST['address_1']);
$address_2	= $connect->real_escape_string($_POST['address_2']);
$city		= $connect->real_escape_string($_POST['city']);
$state		= $connect->real_escape_string($_POST['state']);
$zip		= $connect->real_escape_string($_POST['zip']);
$email		= $connect->real_escape_string($_POST['email']);
$phone		= $connect->real_escape_string($_POST['phone']);
$total_amount = $connect->real_escape_string($_POST['total']);
		

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
$displayAmount = $amount = $orderData['amount'];

$data = [
    "key"               => $keyId,
    "amount"            => $amount,
    "name"              => $first_name.' '.$last_name,
    "description"       => 'PikxiPetals',
    "image"             => "",
    "prefill"           => [
    "name"              => $first_name.' '.$last_name,
    "email"             => $email,
    "contact"           => $phone,
    ],
    "notes"             => [
    "address"           => $address_1,
    "merchant_order_id" => $order_id,
    ],
    "theme"             => [
    "color"             => "#F37254"
    ],
    "order_id"          => $razorpayOrderId,
];

$json = json_encode($data);

?>

<button id="rzp-button1" class="btn btn-primary">Pay with Razorpay</button>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<form name='razorpayform' action="verify.php" method="POST">
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
