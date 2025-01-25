<?php
session_start();
include 'config.php'; // Database connection

$alertMessage = ''; // Placeholder for the alert message
$successMessage = ''; // Placeholder for the success message
$showAlert = false;  // Flag to control if alert should be shown

// Check if login form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if username and password fields are set in the form
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
 
        // Query the database to check if the user exists by username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Successful login, set session and update user status
            $_SESSION['user_id'] = $user['id'];

            // Update user status to 'online'
            $stmt = $pdo->prepare("UPDATE users SET user_status = 'online' WHERE id = ?");
            $stmt->execute([$user['id']]);

            // Set the success message for the alert
            $successMessage = "Login successful! Redirecting to your dashboard...";
            $showAlert = true; // Trigger alert to show success message
        } else {
            // Invalid credentials
            $alertMessage = "Invalid login credentials!";
            $showAlert = true; // Trigger alert to show error message
        }
    } else {
        // Handle missing username or password in the POST data
        $alertMessage = "Please enter both username and password.";
        $showAlert = true; // Trigger alert for missing fields
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Taskify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 font-sans bg-cover" style="background-image: url('https://images.unsplash.com/photo-1507368891075-6a2a9e6ad8f3');">
    <div class="max-w-md mx-auto bg-white bg-opacity-70 p-8 rounded shadow-md mt-10">
        <h2 class="text-2xl font-bold text-center text-gray-800">Login to Taskify</h2>
        <form method="POST">
            <div class="mt-4">
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" required class="mt-1 p-2 border rounded w-full" placeholder="Enter your username">
            </div>

            <div class="mt-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required class="mt-1 p-2 border rounded w-full" placeholder="Enter your password">
            </div>

            <button type="submit" class="mt-4 bg-blue-500 text-white p-2 rounded w-full hover:bg-blue-600 transition duration-200">Login</button>
        </form>
        <p class="mt-4 text-center">Don't have an account? <a href="register.php" class="text-blue-500">Register</a></p>
    </div>

    <!-- SweetAlert Message for failed login or success -->
    <?php if ($showAlert): ?>
    <script>
        // Show SweetAlert for success or failure
        <?php if ($successMessage): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?php echo $successMessage; ?>'
        }).then(() => {
            window.location.href = 'dashboard.php'; // Redirect to dashboard after OK
        });
        <?php elseif ($alertMessage): ?>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '<?php echo $alertMessage; ?>'
        });
        <?php endif; ?>
    </script>
    <?php endif; ?>
</body>
</html>
