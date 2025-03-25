<?php
include("includes/connection.php");
$title = "Sign Up - Expense Splitter";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include("includes/head.php");?>
</head>
<body class="back">
    <!-- Navbar -->
    <?php include("includes/nav_home.php")?>
    <!-- Signup Form -->
    <div class="container" style="min-height:calc(100vh - 200px);">
		<div class="row">
			<div class="col-md-6 offset-md-3">
        		<div class="card p-4 loginf">
					<h2 class="text-center">Sign Up</h2>
					<div id="signupMessage"></div>
					<form id="signupForm">
						<div class="mb-3">
							<label for="name" class="form-label">Full Name</label>
							<input type="text" name="name" class="form-control" placeholder="Full Name" required>
							<span id="nameError" class="error-message"></span>
						</div>
						<div class="mb-3">
							<label for="email" class="form-label">Email Address</label>
							<input type="email" name="email" class="form-control" placeholder="Email" required>
							<span id="emailError" class="error-message"></span>
						</div>
						<div class="mb-3">
							<label for="mobile" class="form-label">Mobile Number</label>
							<input type="text" name="mobile" class="form-control" placeholder="Mobile Number" required>
							<span id="mobileError" class="error-message"></span>
						</div>
						<div class="mb-3">
							<label for="upi" class="form-label">UPI ID</label>
							<input type="text" name="upi" class="form-control" placeholder="UPI ID" required>
							<span id="upiError" class="error-message"></span>
						</div>
						<div class="mb-3">
							<label for="password" class="form-label">Password</label>
							<input type="password" name="password" class="form-control" placeholder="Password" required>
							<span id="passwordError" class="error-message"></span>
						</div>
						<div class="mb-3">
							<label for="confirmPassword" class="form-label">Confirm Password</label>
							<input type="password" name="confirmPassword" class="form-control" placeholder="Confirm Password" required>
							<span id="confirmPasswordError" class="error-message"></span>
						</div>
						<div class="mb-3">
							<button type="submit" class="btn btp w-100 mt-3">Sign Up</button>
						</div>
					</form>
					<p class="mt-3 text-center">Already have an account? <a href="login.php" class="text-light">Login</a></p>
				</div>
   			</div>
		</div>
	</div>

    <!-- Footer -->
    <?php include("includes/footer.php");?>
	
	 <script>
        document.getElementById("signupForm").addEventListener("submit", function(event) {
            event.preventDefault();
        
            const formData = new FormData(this);
            const submitButton = document.querySelector(".btp");
        
            const errorContainers = {
                name: document.getElementById("nameError"),
                email: document.getElementById("emailError"),
                mobile: document.getElementById("mobileError"),
                upi: document.getElementById("upiError"),
                password: document.getElementById("passwordError"),
                confirmPassword: document.getElementById("confirmPasswordError"),
            };
        
            Object.values(errorContainers).forEach(errorContainer => errorContainer.textContent = "");
        
            submitButton.textContent = "Processing...";
            submitButton.disabled = true;
        
            fetch("sign_up.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitButton.textContent = "Sign Up";
                submitButton.disabled = false;
        
                if (data.status === "success") {
                    window.location.href = "login.php";
                } else if (data.status === "error") {
                    console.log("Error:", data.errors);
                    data.errors.forEach(error => {
                        if (error.includes("Full name")) errorContainers.name.textContent = error;
                        else if (error.includes("email")) errorContainers.email.textContent = error;
                        else if (error.includes("Mobile number")) errorContainers.mobile.textContent = error;
                        else if (error.includes("UPI ID")) errorContainers.upi.textContent = error;
                        else if (error.includes("Password must")) errorContainers.password.textContent = error;
                        else if (error.includes("Passwords do not match")) {
                            errorContainers.password.textContent = "Passwords do not match.";
                            errorContainers.confirmPassword.textContent = "Passwords do not match.";
                        }
                    });
                }
            })
            .catch(error => {
                submitButton.textContent = "Sign Up";
                submitButton.disabled = false;
                console.error("Request Error:", error);
            });
        });
        </script>
        
</body>
</html>