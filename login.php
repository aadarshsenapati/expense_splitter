<?php
include("includes/connection.php");
$title = "Login - Expense Splitter";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include("includes/head.php");?>
</head>
<body class="back">

    <!-- Navbar -->
    <?php include("includes/nav_home.php")?>

    <!-- Login Form -->
    <div class="container" style="min-height:calc(100vh - 200px);">
		<div class="row">
			<div class="col-md-6 offset-md-3">
				<div class="card p-4 loginf">
					<h2 class="text-center">Login</h2>
					<form id="loginForm">
						<div class="mb-3">
							<label for="email" class="form-label">Email Address</label>
							<input type="email" class="form-control" name="email" id="email" placeholder="Enter your email" required>
							<div class="text-danger mt-1" id="emailError"></div>
						</div>
						<div class="mb-3">
							<label for="password" class="form-label">Password</label>
							<input type="password" class="form-control" name="password" id="password" placeholder="Enter your password" required>
							<div class="text-danger mt-1" id="passwordError"></div>
						</div>
						<div id="loginError" class="text-danger text-center mb-3"></div>
						<button type="submit" class="btn btp w-100 mt-3">Login</button>
					</form>
					<p class="mt-3 text-center">Don't have an account? <a href="signup.php" class="text-light">Sign Up</a></p>
				</div>
			</div>
		</div>        
    </div>

    <!-- Footer -->
    <?php include("includes/footer.php");?>


    <script>
    document.getElementById("loginForm").addEventListener("submit", async function(event) {
        event.preventDefault(); // Prevent default form submission

        const formData = new FormData(this);
        const submitButton = document.querySelector(".btp");
        const loginError = document.getElementById("loginError");
        const emailError = document.getElementById("emailError");
        const passwordError = document.getElementById("passwordError");

        loginError.textContent = "";
        emailError.textContent = "";
        passwordError.textContent = "";

        submitButton.textContent = "Processing...";
        submitButton.disabled = true;

        try {
            const response = await fetch("log.php", {
                method: "POST",
                body: formData
            });

            const data = await response.json();
            console.log("Response received:", data); 

            submitButton.textContent = "Login";
            submitButton.disabled = false;

            if (data.status === "success") {
                console.log("Login successful! Redirecting...");
                window.location.href = "dashboard.php"; 
            } else if (data.errors) {
                const errorMsg = data.errors.join("\n");

                if (errorMsg.includes("Email")) {
                    emailError.textContent = errorMsg;
                } else if (errorMsg.includes("Password")) {
                    passwordError.textContent = errorMsg;
                } else {
                    loginError.textContent = errorMsg;
                }

                console.warn("Login failed:", errorMsg); 
            }
        } catch (error) {
            submitButton.textContent = "Login";
            submitButton.disabled = false;
            console.error("Error:", error);
            loginError.textContent = "An error occurred. Please try again.";
        }
    });
    </script>

</body>
</html>