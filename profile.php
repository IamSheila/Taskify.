<?php
session_start();
include 'config.php'; // Database connection

// Fetch user data from the database
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$profile_photo = $user['profile_photo'];

$updateSuccess = false; // To track whether the update was successful


// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $mobile_number = $_POST['mobile_number'];
    $status = $_POST['user_status'];
    $selected_photo = $profile_photo; // Start with current photo

    // Handle file upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == UPLOAD_ERR_OK) {
        // Get file info
        $fileTmpPath = $_FILES['profile_photo']['tmp_name'];
        $fileName = $_FILES['profile_photo']['name'];
        $fileSize = $_FILES['profile_photo']['size'];
        $fileType = $_FILES['profile_photo']['type'];

        // Read the file into a binary string
        $selected_photo = file_get_contents($fileTmpPath); // New photo selected
    }

    // Update query for other profile details
    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, gender = ?, address = ?, city = ?, country = ?, mobile_number = ?, profile_photo = ? WHERE id = ?");
    $stmt->execute([$full_name, $gender, $address, $city, $country, $mobile_number, $selected_photo, $_SESSION['user_id']]);

    // Set session flag for successful update
    $_SESSION['update_success'] = true;

    // Redirect to the profile page to show SweetAlert
    header("Location: profile.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Taskify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
                    <li>
                        <a href="dashboard.php" class="block py-2 text-white hover:bg-blue-700 hover:text-white rounded px-4">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="profile.php" class="block py-2 text-white hover:bg-blue-700 hover:text-white rounded px-4">
                            <i class="fas fa-user mr-2"></i>Profile
                        </a>
                    </li>
                    <li>
                        <a href="create_task.php" class="block py-2 text-white hover:bg-blue-700 hover:text-white rounded px-4">
                            <i class="fas fa-plus mr-2"></i>Create Task
                        </a>
                    </li>
                    <li>
                        <a href="history.php" class="block py-2 text-white hover:bg-blue-700 hover:text-white rounded px-4">
                            <i class="fas fa-history mr-2"></i>Task History
                        </a>
                    </li>
                    <li>
                        <a href="change_password.php" class="block py-2 text-white hover:bg-blue-700 hover:text-white rounded px-4">
                            <i class="fas fa-lock mr-2"></i>Change Password
                        </a>
                    </li>
                    <li>
                        <a href="logout.php" class="block py-2 text-white hover:bg-blue-700 hover:text-white rounded px-4">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main content -->
        <div class="flex-1 p-6 sm:ml-64">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Edit Profile</h1>

            <!-- Edit Profile Form -->
            <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md">
                <!-- Profile Photo Section -->
                <div class="mb-4">
                    <label class="block font-medium text-gray-600">Current Profile Photo</label>
                    <div class="flex items-center space-x-4">
                        <!-- Display current image -->
                        <?php if ($profile_photo): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($profile_photo) ?>" alt="Profile Photo" class="w-32 h-32 rounded-full mb-4">
                        <?php else: ?>
                            <p>No profile photo available or the file does not exist</p>
                        <?php endif; ?>
                        <!-- Camera Icon for "Change Photo" -->
                        <button type="button" id="changePhotoBtn" class="text-blue-500 hover:underline">
                            <i class="fas fa-camera fa-2x"></i>
                        </button>
                    </div>
                </div>

                <!-- Hidden file input for profile photo change -->
                <div id="fileInputSection" class="hidden mb-4">
                    <label for="profile_photo" class="block font-medium text-gray-600">Upload New Profile Photo</label>
                    <input type="file" id="profile_photo" name="profile_photo" class="mt-1 block w-full p-2 border border-gray-300 rounded">
                </div>

                <!-- Display Username, Email, Status, and Created Date -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Username (Read-Only) -->
                    <div class="mb-4">
                        <label for="username" class="block font-medium text-gray-600">Username</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded" readonly>
                    </div>

                    <!-- Email (Read-Only) -->
                    <div class="mb-4">
                        <label for="email" class="block font-medium text-gray-600">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded" readonly>
                    </div>
                </div>

                <!-- Form Fields Side by Side -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Full Name -->
                    <div class="mb-4">
                        <label for="full_name" class="block font-medium text-gray-600">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>
                    </div>

                    <!-- Gender -->
                    <div class="mb-4">
                        <label for="gender" class="block font-medium text-gray-600">Gender</label>
                        <select id="gender" name="gender" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>
                            <option value="male" <?= $user['gender'] == 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= $user['gender'] == 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= $user['gender'] == 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Address -->
                    <div class="mb-4">
                        <label for="address" class="block font-medium text-gray-600">Address</label>
                        <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['address']) ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>
                    </div>

                    <!-- City -->
                    <div class="mb-4">
                        <label for="city" class="block font-medium text-gray-600">City</label>
                        <input type="text" id="city" name="city" value="<?= htmlspecialchars($user['city']) ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Country -->
                    <div class="mb-4">
                        <label for="country" class="block font-medium text-gray-600">Country</label>
                        <input type="text" id="country" name="country" value="<?= htmlspecialchars($user['country']) ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>
                    </div>

                    <!-- Mobile Number -->
                    <div class="mb-4">
                        <label for="mobile_number" class="block font-medium text-gray-600">Mobile Number</label>
                        <input type="text" id="mobile_number" name="mobile_number" value="<?= htmlspecialchars($user['mobile_number']) ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded" required>
                    </div>
                </div>

                <!-- Status, and Created Date -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Status (Read-Only) -->
                    <div class="mb-4">
                        <label for="user_status" class="block font-medium text-gray-600">User Status</label>
                        <input type="text" id="user_status" name="user_status" value="<?= htmlspecialchars($user['user_status']) ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded" readonly>
                    </div>

                    <!-- Created Date (Read-Only) -->
                    <div class="mb-4">
                        <label for="created_at" class="block font-medium text-gray-600">Account Created</label>
                        <input type="created_at" id="created_at" name="created_at" value="<?= htmlspecialchars($user['created_at']) ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded" readonly>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <button type="submit" class="inline-block bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- SweetAlert Success Message (only if update is successful) -->
    <?php if (isset($_SESSION['update_success']) && $_SESSION['update_success'] === true): ?>
        <script>
            Swal.fire({
                title: 'Success!',
                text: 'Your profile has been updated successfully.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'dashboard.php'; // Redirect to dashboard after SweetAlert closes
            });
        </script>
        <?php unset($_SESSION['update_success']); ?> <!-- Clear the session flag -->
    <?php endif; ?>


    <!-- Sidebar Toggle Script -->
    <script>
        const sidebarToggle = document.getElementById("sidebarToggle");
        const sidebar = document.getElementById("sidebar");

        // Toggle sidebar visibility on button click
        sidebarToggle.addEventListener("click", () => {
            sidebar.classList.toggle("hidden");
        });

        // Toggle the file input for changing profile photo
        document.getElementById("changePhotoBtn").addEventListener("click", function() {
            document.getElementById("fileInputSection").classList.toggle("hidden");
        });
    </script>

</body>
</html>
