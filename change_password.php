<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];

    // Fetch current password from DB
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user && password_verify($currentPassword, $user['password'])) {
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newPasswordHash, $_SESSION['user_id']]);

        // If password updated successfully, trigger SweetAlert success
        $successMessage = true; // Used to trigger SweetAlert success
    } else {
        $errorMessage = true; // Used to trigger SweetAlert error
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Taskify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 font-sans">

    <!-- Header -->
    <header class="bg-blue-500 text-white p-4 fixed top-0 w-full z-50">
        <div class="flex items-center justify-between">
            <button id="sidebarToggle" class="text-white sm:hidden hover:bg-blue-700 p-2 rounded">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="text-2xl font-semibold">Taskify Dashboard</h1>
            <div></div> <!-- Empty div to align the header content -->
        </div>
    </header>

    <!-- Sidebar -->
    <div class="flex h-screen mt-16"> <!-- Add mt-16 to offset the header height -->
        <div id="sidebar" class="w-64 bg-blue-500 text-white p-4 fixed sm:block h-full sm:top-10 hidden">
            <nav class="mt-6">
                <ul>
                    <li><a href="dashboard.php" class="block py-2 text-white hover:bg-blue-700 hover:text-white rounded px-4"><i class="fas fa-tachometer-alt mr-2"></i>Dashboard</a></li>
                    <li><a href="profile.php" class="block py-2 text-white hover:bg-blue-700 hover:text-white rounded px-4"><i class="fas fa-user mr-2"></i>Profile</a></li>
                    <li><a href="create_task.php" class="block py-2 text-white hover:bg-blue-700 hover:text-white rounded px-4"><i class="fas fa-plus mr-2"></i>Create Task</a></li>
                    <li><a href="history.php" class="block py-2 text-white hover:bg-blue-700 hover:text-white rounded px-4"><i class="fas fa-history mr-2"></i>Task History</a></li>
                    <li><a href="change_password.php" class="block py-2 text-white hover:bg-blue-700 hover:text-white rounded px-4"><i class="fas fa-lock mr-2"></i>Change Password</a></li>
                    <li><a href="logout.php" class="block py-2 text-white hover:bg-blue-700 hover:text-white rounded px-4"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main content -->
        <div class="flex-1 p-6 sm:ml-64">
            <!-- Change Password Form -->
            <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Change Password</h2>
                <form method="POST" action="change_password.php">
                    <div class="mb-4">
                        <input type="password" name="current_password" placeholder="Current Password" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                    </div>
                    <div class="mb-4">
                        <input type="password" name="new_password" placeholder="New Password" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                    </div>
                    <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">Change Password</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const sidebarToggle = document.getElementById("sidebarToggle");
        const sidebar = document.getElementById("sidebar");

        sidebarToggle.addEventListener("click", () => {
            sidebar.classList.toggle("hidden");
        });

        // Check if password update was successful and trigger SweetAlert
        <?php if (isset($successMessage) && $successMessage): ?>
            Swal.fire({
                icon: 'success',
                title: 'Password Updated!',
                text: 'Your password has been successfully updated.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to the dashboard after successful password update
                    window.location.href = 'dashboard.php';
                }
            });
        <?php elseif (isset($errorMessage) && $errorMessage): ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Current password is incorrect.',
                confirmButtonText: 'Try Again'
            });
        <?php endif; ?>
    </script>

</body>
</html>
