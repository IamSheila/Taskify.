<?php
session_start();
include 'config.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle task creation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];
    $priority = $_POST['priority'];

    $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, deadline, priority) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $title, $description, $deadline, $priority]);
    header('Location: dashboard.php'); // Refresh the page
}

// Fetch task counts for each status
$stmt = $pdo->prepare("SELECT status, COUNT(*) AS count FROM tasks WHERE user_id = ? GROUP BY status");
$stmt->execute([$_SESSION['user_id']]);
$taskCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch 5 latest tasks
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$latestTasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Taskify</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
        <div class="flex-1 p-6 overflow-auto sm:ml-64"> <!-- Add ml-64 on large screens to offset the sidebar -->
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Welcome to Your Dashboard</h1>

            <!-- Task Status Count Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                <div class="bg-green-500 p-6 rounded-lg text-white shadow-md">
                    <h3 class="text-lg font-semibold">Completed Tasks</h3>
                    <p class="text-2xl"><?= isset($taskCounts['completed']) ? $taskCounts['completed'] : 0 ?></p>
                </div>
                <div class="bg-yellow-500 p-6 rounded-lg text-white shadow-md">
                    <h3 class="text-lg font-semibold">In Progress</h3>
                    <p class="text-2xl"><?= isset($taskCounts['in-progress']) ? $taskCounts['in-progress'] : 0 ?></p>
                </div>
                <div class="bg-red-500 p-6 rounded-lg text-white shadow-md">
                    <h3 class="text-lg font-semibold">New Tasks</h3>
                    <p class="text-2xl"><?= isset($taskCounts['pending']) ? $taskCounts['pending'] : 0 ?></p>
                </div>
            </div>

            <!-- DataGridView-like Task Summary Grid -->
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Latest Tasks Summary</h2>
            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Task Title</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Description</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Deadline</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Priority</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Status</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($latestTasks as $task): ?>
                            <tr class="border-b">
                                <td class="px-4 py-2"><?= htmlspecialchars($task['title']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($task['description']) ?></td>
                                <td class="px-4 py-2"><?= $task['deadline'] ?></td>
                                <td class="px-4 py-2"><?= ucfirst($task['priority']) ?></td>
                                <td class="px-4 py-2">
                                    <!-- Apply text color based on status -->
                                    <?php 
                                    $statusClass = '';
                                    switch ($task['status']) {
                                        case 'completed':
                                            $statusClass = 'text-green-500';
                                            break;
                                        case 'in-progress':
                                            $statusClass = 'text-yellow-500';
                                            break;
                                        case 'pending':
                                            $statusClass = 'text-red-500';
                                            break;
                                    }
                                    ?>
                                    <span class="px-4 py-2 rounded <?= $statusClass ?>"><?= ucfirst($task['status']) ?></span>
                                </td>
                                <td class="px-4 py-2">
    <?php if ($task['status'] !== 'completed'): ?>
        <a href="edit_task.php?task_id=<?= $task['id'] ?>" class="text-blue-500 hover:underline">Edit</a> |
    <?php endif; ?>
    <a href="delete_task.php?task_id=<?= $task['id'] ?>" class="text-red-500 hover:underline">Delete</a>
</td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar Toggle Script -->
    <script>
        const sidebarToggle = document.getElementById("sidebarToggle");
        const sidebar = document.getElementById("sidebar");

        // Toggle sidebar visibility on button click
        sidebarToggle.addEventListener("click", () => {
            sidebar.classList.toggle("hidden");
        });

        // Close sidebar when clicking outside of it
        document.addEventListener("click", (event) => {
            if (!sidebar.contains(event.target) && event.target !== sidebarToggle) {
                sidebar.classList.add("hidden");
            }
        });

    </script>

</body>
</html>
