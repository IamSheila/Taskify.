<?php
session_start();
include 'config.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];
    $priority = $_POST['priority'];

    // Insert task into the database
    $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, deadline, priority) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $title, $description, $deadline, $priority]);

    // Redirect to the create_task.php page with a success parameter to trigger SweetAlert
    echo "<script>window.location = 'create_task.php?success=true';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task - Taskify</title>
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
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Create New Task</h1>

            <form id="createTaskForm" method="POST" action="create_task.php" class="bg-white p-6 rounded-lg shadow-md">
                <div class="mb-4">
                    <label for="title" class="block text-gray-600 font-medium">Task Title</label>
                    <input type="text" id="title" name="title" placeholder="Enter task title" required
                        class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-gray-600 font-medium">Description</label>
                    <textarea id="description" name="description" placeholder="Enter task description" required
                        class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="mb-4">
                    <label for="deadline" class="block text-gray-600 font-medium">Deadline</label>
                    <input type="date" id="deadline" name="deadline" required
                        class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label for="priority" class="block text-gray-600 font-medium">Priority</label>
                    <select name="priority" id="priority" required
                        class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 transition duration-200">
                    Create Task
                </button>
            </form>
        </div>
    </div>

    <!-- SweetAlert on successful task creation -->
    <script>
        // Check if the URL has a 'success' query parameter
        if (window.location.search.indexOf('success=true') !== -1) {
            Swal.fire({
                title: 'Task Created!',
                text: 'Do you want to create another task?',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Yes, create another',
                cancelButtonText: 'No, go to dashboard'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'create_task.php'; // Stay on the create task page
                } else {
                    window.location.href = 'dashboard.php'; // Redirect to dashboard
                }
            });
        }
    </script>


    <!-- Sidebar Toggle Script -->
    <script>
        const sidebarToggle = document.getElementById("sidebarToggle");
        const sidebar = document.getElementById("sidebar");

        sidebarToggle.addEventListener("click", () => {
            sidebar.classList.toggle("hidden");
        });
    </script>

</body>
</html>
