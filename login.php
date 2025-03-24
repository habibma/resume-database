<?php
session_start();
require_once "pdo.php";

$salt = 'XyZzy12*_';
$failure = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['email']) || empty($_POST['pass'])) {
        $_SESSION['error'] = "Both fields must be filled out";
        header("Location: login.php");
        exit();
    } else {
        $check = hash('md5', $salt.$_POST['pass']);

        $stmt = $pdo->prepare("SELECT user_id, `name` FROM users WHERE email = :em AND password = :pw");
        $stmt->execute(array(':em' => $_POST['email'], ':pw' => $check));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row !== false) {
            $_SESSION['name'] = $row['name'];
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['success'] = "Logged in successfully";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Incorrect email or password";
            header("Location: login.php");
            exit();
        }
    }
}
?>

<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Habib Mote - login</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            function doValidate() {
                console.log('Validating...');
                try {
                    pw = document.getElementById('id_1723').value;
                    em = document.getElementById('email').value;
                    console.log("validating em="+em)
                    console.log("Validating pw="+pw);
                    if (em == null || em == "" || pw == null || pw == "") {
                        alert("Both fields must be filled out");
                        return false;
                    }
                    if (em.indexOf('@') == -1) {
                        alert("Invalid email address");
                        return false;
                    }
                    return true;
                } catch(e) {
                    console.log(e);
                    return false;
                }
                return false;
            }
        </script>
    </head>
    <body>
        <div class="h-screen">
            <header class="bg-gray-800 text-white py-4 px-8 flex justify-between items-center">
                <h1 class="text-xl font-bold">Log In</h1>
                <a href="index.php" class="text-white underline">Back to Home</a>
            </header>
            <main class="p-4 flex justify-center items-center h-full">
                <div class="container bg-white p-8 rounded shadow-md w-96">
                    <h1 class="text-2xl mb-4">Please Log In</h1>
                    <?php
                        if (isset($_SESSION['error'])) {
                        echo '<p class="text-red-500">'.htmlentities($_SESSION['error'])."</p>\n";
                        unset($_SESSION['error']);
                        }
                    ?>
                    <form method="POST">
                        <div class="mb-4">
                            <label for="email" class="block">Email</label>
                            <input type="text" name="email" id="email" class="border p-2 w-full ">
                        </div>
                        <div class=mb-4>
                            <label for="id_1723" class="block">Password</label>
                            <input type="text" name="pass" id="id_1723" class="border p-2 w-full ">
                        </div>
                        <input type="submit" onclick="return doValidate();" value="Log In" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-500">
                    </form>
                </div>
            </main>
            <footer class="bg-gray-800 p-4 text-white text-center">
                <p>&copy; Habib Mote</p>
            </footer>
        </div>
    </body>
</html>
