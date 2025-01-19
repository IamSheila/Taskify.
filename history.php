<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Pagination setup
$tasksPerPage = 8;  // Set tasks per page to 8
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;  // Get current page from query string
$offset = ($currentPage - 1) * $tasksPerPage;  // Calculate offset for SQL query

// Fetch tasks with pagination and search query (if exists)
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = :user_id AND (title LIKE :search OR description LIKE :search) ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(':search', "%$searchQuery%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $tasksPerPage, PDO::PARAM_INT);  // Limit to 10 tasks
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);  // Offset for pagination
$stmt->execute();
$latestTasks = $stmt->fetchAll();

// Fetch total tasks count for pagination (without limit and offset)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = :user_id AND (title LIKE :search OR description LIKE :search)");
$stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(':search', "%$searchQuery%", PDO::PARAM_STR);
$stmt->execute();
$totalTasks = $stmt->fetchColumn();
$totalPages = ceil($totalTasks / $tasksPerPage);  // Total number of pages
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task History - Taskify</title>
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
        <div class="flex-1 p-6 overflow-auto sm:ml-64">
            
            <!-- Search Bar -->
<div class="mb-4 flex flex-col sm:flex-row sm:justify-between items-center">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4 sm:mb-0">Latest Tasks Summary</h2>
    <form method="GET" class="flex items-center w-full sm:w-auto">
        <input type="text" name="search" id="search" value="<?= htmlspecialchars($searchQuery) ?>" class="px-4 py-2 border border-gray-300 rounded-lg w-full sm:w-80" placeholder="Search tasks...">
        <!-- Search icon instead of button -->
        <button type="submit" class="ml-2 p-2 text-white bg-blue-500 rounded-full hover:bg-blue-600">
            <i class="fas fa-search"></i>
        </button>
        <!-- Clear icon instead of button -->
        <button type="button" onclick="clearSearch()" class="ml-2 p-2 text-white bg-red-500 rounded-full hover:bg-red-600">
            <i class="fas fa-times"></i>
        </button>
    </form>
</div>


            <!-- DataGrid/Table Layout for Tasks -->
            <div class="overflow-x-auto bg-white shadow-md rounded-lg p-4">
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
                                <td class="px-4 py-2"><?= (new DateTime($task['deadline']))->format('Y-m-d') ?></td> <!-- Format deadline to show only date -->
                                <td class="px-4 py-2"><?= ucfirst($task['priority']) ?></td>
                                <td class="px-4 py-2">
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

            <!-- Pagination -->
<div class="mt-4 flex justify-between items-center">
    <div class="text-sm text-gray-600">
        Showing <?= ($currentPage - 1) * $tasksPerPage + 1 ?> to <?= min($currentPage * $tasksPerPage, $totalTasks) ?> entries
    </div>

    <div class="space-x-2">
        <!-- Previous button with icon -->
        <?php if ($currentPage > 1): ?>
            <a href="?page=<?= $currentPage - 1 ?>&search=<?= htmlspecialchars($searchQuery) ?>" class="bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 flex items-center">
                <i class="fas fa-chevron-left mr-2"></i> Previous
            </a>
        <?php endif; ?>

        <!-- Next button with icon -->
        <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?= $currentPage + 1 ?>&search=<?= htmlspecialchars($searchQuery) ?>" class="bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 flex items-center">
                <i class="fas fa-chevron-right mr-2"></i> Next
            </a>
        <?php endif; ?>
    </div>
</div>

        </div>
    </div>

    <script>
        const sidebarToggle = document.getElementById("sidebarToggle");
        const sidebar = document.getElementById("sidebar");

        sidebarToggle.addEventListener("click", () => {
            sidebar.classList.toggle("hidden");
        });

        function clearSearch() {
            document.getElementById("search").value = '';
            window.location.href = 'history.php';
        }
    </script>

</body>
</html>
