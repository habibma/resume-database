<?php
session_start();
require_once "pdo.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Habib Mote - Profile Database</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="h-screen">
        <header class="bg-gray-800 text-white py-4 px-8 flex justify-between items-center">
            <h1 class="text-xl font-bold">Profile Registry</h1>
            <a href="index.php" class="text-white underline">Back to Home</a>
        </header>
        <main class="p-4 flex justify-center items-center h-full">
            <div class="container bg-white p-8 rounded shadow-md lg:w-1/2">
                <?php
                    // Display success or error messages
                    if (isset($_SESSION['success'])) {
                        echo '<p style="color:green">'.htmlentities($_SESSION['success'])."</p>\n";
                        unset($_SESSION['success']);
                    }
                    if (isset($_SESSION['error'])) {
                        echo '<p style="color:red">'.htmlentities($_SESSION['error'])."</p>\n";
                        unset($_SESSION['error']);
                    }
                    // Check if the user is logged in
                    if (isset($_SESSION['name'])) {
                        echo '<p class="text-lg">Welcome, '.htmlentities($_SESSION['name']).'!</p>';
                        echo '<p class="underline"><a href="logout.php">Logout</a></p>';
                    } else {
                        echo '<p class="underline text-red-500"><a href="login.php">Please log in</a></p>';
                    }
                    ?>
                <h2 class="text-xl font-bold my-4">Profiles</h2>
                <table style="border-collapse: collapse; width: 100%;">
                    <tr>
                        <th>Name</th>
                        <th>Headline</th>
                        <?php if (isset($_SESSION['user_id'])) { echo "<th>Action</th>"; } ?>
                    </tr>
                    <?php
                        // Fetch resumes from the database
                        $stmt = $pdo->query("SELECT profile.profile_id, users.user_id, first_name, last_name, headline FROM users JOIN `profile` ON users.user_id = profile.user_id");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr><td>";
                            echo "<a href= 'view.php?profile_id=". $row['profile_id'] ."' class='underline'>" . htmlentities($row['first_name'] . " " .$row['last_name']) . "</a>";
                            echo "</td><td>";
                            echo htmlentities($row['headline']);
                            echo "</td>";
                            // Show edit/delete options for logged-in users
                            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']) {
                                echo "<td>";
                                echo '<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ';
                                echo '<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>';
                                echo "</td>";
                            }
                            echo "</tr>\n";
                        }
                    ?>
                </table>
                <?php
                    if (isset($_SESSION['user_id'])) {
                        echo '<p class="w-full my-6"><a class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500" href="add.php">Add New Entry</a></p>';
                    }
                    ?>
            </div>
        </main>
        <footer class="bg-gray-800 p-4 text-white text-center">
            <p>&copy; Habib Mote</p>
        </footer>
    </div>
</body>
</html>
