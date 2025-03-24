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
$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$row){
    $_SESSION['error'] = "Could not load the profile";
    header('Location: index.php');
    return;
}

// Fetch positions
$stmt = $pdo->prepare("SELECT year, description FROM Position WHERE profile_id = :pid ORDER BY rank");
$stmt->execute(array(":pid" => $_GET['profile_id']));
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch education details
$stmt = $pdo->prepare("SELECT year, name FROM Education 
                       JOIN Institution ON Education.institution_id = Institution.institution_id 
                       WHERE profile_id = :pid ORDER BY rank");

$stmt->execute(array(":pid" => $_GET['profile_id']));
$education = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Habib Mote - Profile Information</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="min-h-screen">
        <header class="bg-gray-800 text-white py-4 px-8 flex justify-between items-center">
            <h1 class="text-xl font-bold">Profile Information</h1>
            <a href="index.php" class="text-white underline">Back to Home</a>
        </header>
        <main class="p-4 flex justify-center items-center h-full">
            <div class="container bg-white p-8 rounded shadow-md lg:w-1/2">
                <h2 class="text-xl font-bold my-4">Profiles</h2>
                <?php foreach($row as $element)?>
                    <h2 class="text-lg mt-2 font-bold">First Name:</h2>
                    <p><?= htmlentities($element['first_name']) ?></p>
                    <h2 class="text-lg mt-2 font-bold">Last Name:</h2>
                    <p><?= htmlentities($element['last_name']) ?></p>
                    <h2 class="text-lg mt-2 font-bold">Email:</h2>
                    <p><?= htmlentities($element['email']) ?></p>
                    <h2 class="text-lg mt-2 font-bold">Headline:</h2>
                    <p><?= htmlentities($element['headline']) ?></p>
                    <h2 class="text-lg mt-2 font-bold">Summary:</h2>
                    <p><?= htmlentities($element['summary']) ?></p>
                    <?php if (!empty($positions)) : ?>
                        <h2 class="text-lg mt-2 font-bold">Positions</h2>
                        <ul>
                            <?php foreach ($positions as $pos) : ?>
                                <li><?= htmlentities($pos['year']) ?>: <?= htmlentities($pos['description']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (!empty($education)) : ?>
                        <h2 class="text-lg mt-2 font-bold">Education</h2>
                        <ul>
                            <?php foreach ($education as $edu) : ?>
                                <li><?= htmlentities($edu['year']) ?>: <?= htmlentities($edu['name']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <p class="mt-8" ><a href="index.php" class="underline">Done!</a></p>
            </div>
        </main>
        <footer class="bg-gray-800 p-4 text-white text-center">
            <p>&copy; Habib Mote</p>
        </footer>
    </div>
</body>
</html>
