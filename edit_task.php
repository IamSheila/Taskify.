<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$taskId = $_GET['task_id'];

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$taskId, $_SESSION['user_id']]);
$task = $stmt->fetch();

if (!$task) {
    echo "Task not found";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];
    $priority = $_POST['priority'];
    $status = $_POST['status'];

    // If the task is already completed, do not allow status change
    if ($task['status'] != 'completed') {
        $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, deadline = ?, priority = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $description, $deadline, $priority, $status, $taskId]);
    } else {
        $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, deadline = ?, priority = ? WHERE id = ?");
        $stmt->execute([$title, $description, $deadline, $priority, $taskId]);
    }

    // Set a flag to indicate success and pass the task id for redirection
    $_SESSION['task_update_success'] = true;
    $_SESSION['task_id'] = $taskId;

    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Task - Taskify</title>
    
    <!-- Include Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

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
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Edit Task</h2>

            <!-- Edit Task Form -->
            <form method="POST" action="edit_task.php?task_id=<?= $task['id'] ?>" class="bg-white shadow-md rounded-lg p-6">

                <!-- Task Title -->
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700">Task Title</label>
                    <input type="text" name="title" id="title" value="<?= htmlspecialchars($task['title']) ?>" required class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Task Description -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="4" class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($task['description']) ?></textarea>
                </div>

                <!-- Deadline and Priority -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Deadline -->
                    <div class="mb-4">
                        <label for="deadline" class="block text-sm font-medium text-gray-700">Deadline</label>
                        <input type="date" name="deadline" id="deadline" value="<?= htmlspecialchars((new DateTime($task['deadline']))->format('Y-m-d')) ?>" required class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Priority -->
                    <div class="mb-4">
                        <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                        <select name="priority" id="priority" class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="low" <?= $task['priority'] == 'low' ? 'selected' : '' ?>>Low</option>
                            <option value="medium" <?= $task['priority'] == 'medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="high" <?= $task['priority'] == 'high' ? 'selected' : '' ?>>High</option>
                        </select>
                    </div>
                </div>

                <!-- Status (disabled if task is completed) -->
                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" <?= $task['status'] == 'completed' ? 'disabled' : '' ?> >
                        <option value="pending" <?= $task['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="in-progress" <?= $task['status'] == 'in-progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= $task['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 w-full" <?= $task['status'] == 'completed' ? 'disabled' : '' ?>>Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        // Check if task update success flag is set in the session
        <?php if (isset($_SESSION['task_update_success']) && $_SESSION['task_update_success'] === true): ?>
            Swal.fire({
                icon: 'success',
                title: 'Task updated successfully!',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = "history.php"; // Redirect to history page after alert
            });
            <?php unset($_SESSION['task_update_success']); ?>
        <?php endif; ?>
    </script>

    <script>
        const sidebarToggle = document.getElementById("sidebarToggle");
        const sidebar = document.getElementById("sidebar");

        sidebarToggle.addEventListener("click", () => {
            sidebar.classList.toggle("hidden");
        });
    </script>

</body>
</html>
