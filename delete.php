<?php
session_start();
require_once "pdo.php";

if (!isset($_SESSION['user_id'])){
    die('Access Denied');
}
if (!isset($_GET['profile_id'])){
    $_SESSION['error'] = "Missing profile_id";
    header("Location: index.php");
    return;
}

$stmt = $pdo->prepare('SELECT * FROM `profile` WHERE profile_id = :pid AND user_id = :uid');
$stmt->execute(array(
    ':pid' => $_GET['profile_id'],
    ':uid' => $_SESSION['user_id']
));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$profile){
    $_SESSION['error'] = "Could not load profile";
    header('Location: index.php');
    return;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $stmt = $pdo->prepare('DELETE FROM `profile` WHERE profile.profile_id = :pid AND profile.user_id = :uid');
    $stmt->execute([
        ':pid' => $_POST['profile_id'],
        ':uid' => $_SESSION['user_id']
    ]);
    $_SESSION['success'] = "Resume Deleted!";
    header("Location: index.php");
    return;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Habib Mote - Delete Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="h-screen">
        <header class="bg-gray-800 text-white py-4 px-8 flex justify-between items-center">
            <h1 class="text-xl font-bold">Delete Profile</h1>
            <a href="index.php" class="text-white underline">Back to Home</a>
        </header>
        <main class="p-4 flex justify-center items-center h-full">
            <div class="container bg-white p-8 rounded shadow-md w-96">
                <h1 class="text-xl font-bold my-4">Delete Profile</h1>
                <?php
                    if (isset($_SESSION['error'])) {
                        echo '<p style="color:red">'.htmlentities($_SESSION['error'])."</p>\n";
                        unset($_SESSION['error']);
                    }
                ?>
                <p class="my-4">Are you sure you want to delete this resume?</p>
                <form method="POST">
                    <input type="hidden" name="profile_id" value="<?= $profile['profile_id'] ?>">
                    <p><input type="submit" value="Delete" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500">
                    <a class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-500" href="index.php">Cancel</a></p>
                </form>
            </div>
        </main>
        <footer class="bg-gray-800 p-4 text-white text-center">
            <p>&copy; Habib Mote</p>
        </footer>
    </div>
</body>
</html>