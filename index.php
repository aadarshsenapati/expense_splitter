<?php
include("includes/connection.php");
$title = "Expense Splitter - Landing Page";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include("includes/head.php");?>
</head>
<body>
    <!-- Navbar -->
    <?php include("includes/nav_home.php")?>

    <!-- Hero Section -->
    <header class="bg-light text-center py-5 back">
        <div class="container">
            <h1 class="display-4 candal">Split Expenses with Ease!</h1>
            <p class="lead tinos">Track and settle shared expenses effortlessly.</p>
            <a href="signup.php" class="btn btn-lg btp">Get Started</a>
        </div>
    </header>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container text-center">
            <h2 class="tinos">Why Use Expense Splitter?</h2>
            <div class="row mt-4 justify-content-center g-3">
                <div class="col-md-3 scr">
                    <h4 class="tinos">Simple & Fast</h4>
                    <p>Split bills in seconds with an intuitive interface.</p>
                </div>
                <div class="col-md-3 scr">
                    <h4 class="tinos">Track Expenses</h4>
                    <p>View outstanding balances and payment history.</p>
                </div>

                <div class="col-md-3 scr">
                    <h4 class="tinos">Secure & Private</h4>
                    <p>Your data is safe and only visible to your group.</p>
                </div>
            </div>
        </div>
    </section>
    

    <!-- How It Works -->
    <section id="how-it-works" class="bg-light py-5 back">
        <div class="container text-center">
            <h2 class="tinos">How It Works</h2>
            <div class="row mt-4 justify-content-center g-3">
                <div class="col-md-3 scr">
                    <h4 class="tinos">1. Create a Group</h4>
                    <p>Add friends and start tracking expenses together.</p>
                </div>
                <div class="col-md-3 scr">
                    <h4 class="tinos">2. Add Expenses</h4>
                    <p>Enter bills and let the system calculate splits.</p>
                </div>
                <div class="col-md-3 scr">
                    <h4 class="tinos">3. Settle Up</h4>
                    <p>Track payments and clear outstanding balances.</p>
                </div>
            </div>
        </div>
    </section>
    

    <!-- Footer -->
    <?php include("includes/footer.php");?>
</body>
</html>
