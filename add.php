<?php
session_start();
require_once "pdo.php";

if (!isset($_SESSION['user_id'])){
    die('Access Denied');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['headline']) || empty($_POST['summary']) || empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email'])){
        $_SESSION['error'] = "All fields are required!";
        header("Location: add.php");
        return;
    }
    if (strpos($_POST['email'], '@') === false){
        $_SESSION['error'] = "Email address must contaion @";
        header("Location: add.php");
        return;
    }
    $stmt = $pdo->prepare('INSERT INTO profile(user_id, first_name, last_name, email, headline, summary) VALUES (:uid, :fn, :ln, :em, :hl, :sm)');
    $stmt->execute(array(
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':hl' => $_POST['headline'],
        ':sm' => $_POST['summary']
    ));

    $profile_id = $pdo->lastInsertId();

    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['year' . $i]) || !isset($_POST['desc' . $i])) continue;

        $year = $_POST['year' . $i];
        $desc = $_POST['desc' . $i];

        if (empty($year) || empty($desc)) {
            $_SESSION['error'] = "All fields are required";
            header("Location: add.php");
            exit();
        }
        if (!is_numeric($year)) {
            $_SESSION['error'] = "Year must be numeric";
            header("Location: add.php");
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO Position (profile_id, rank, year, `description`) 
                               VALUES (:pid, :rank, :year, :desc)");
        $stmt->execute([
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc
        ]);
        $stmt = $pdo->prepare("INSERT INTO Education (profile_id, rank, year, institution_id) 
                               VALUES (:pid, :rank, :year, :iid)");
        $stmt->execute([
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':iid' => $institution_id
        ]);
        $rank++;
    }
    
    $_SESSION['success'] = "Profile Added!";
    header("Location: index.php");
    return;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Habib Mote - Add Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"> -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css"></head>
    <script>console.log('username: umsi@umich.edu / pass: php123')</script>
<body>
    <div class="min-h-screen">
        <header class="bg-gray-800 text-white py-4 px-8 flex justify-between items-center">
            <h1 class="text-xl font-bold">Profile Registry</h1>
            <a href="index.php" class="text-white underline">Back to Home</a>
        </header>
        <main class="p-4 flex justify-center items-center h-full">
            <div class="container bg-white p-8 rounded shadow-md w-1/2">
                <h1 class="text-xl font-bold my-4">Add Resume</h1>
                <?php
                    // Show error message
                    if (isset($_SESSION['error'])) {
                        echo '<p style="color:red">'.htmlentities($_SESSION['error'])."</p>\n";
                        unset($_SESSION['error']);
                    }
                ?>
                <form method="post">
                    <fieldset class="mb-4 flex">
                        <div class="w-1/2 pr-2">
                            <label class="block" for="first_name">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="border p-2 w-full">
                        </div>
                        <div class="w-1/2 pr-2">
                            <label class="block" for="last_name">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="border p-2 w-full">
                        </div>
                    </fieldset>
                    <label class="block">Email: <input type="text" name="email" class="border p-2 w-full"></label>
                    <label>Headline: <input type="text" name="headline" class="border p-2 w-full"></label>
                    <label class="block">Summary: <textarea name="summary"class="border p-2 w-full"></textarea></label>
                    <fieldset class="mb-4">
                        <p>Position: <input type="button" id="addPos" value="+" class="cursor-pointer bg-gray-500 px-2"></p>
                        <div id="position_fields"></div>
                    </fieldset>
                    <fieldset class="mb-4">
                        <p>Education: <input type="button" id="addEdu" value="+" class="cursor-pointer bg-gray-500 px-2"></p>
                        <div id="education_fields"></div>
                    </fieldset>
                    <p><input type="submit" value="Add" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500">
                    <a class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-500" href="index.php">Cancel</a></p>
                </form>
            </div>
        </main>
        <footer class="bg-gray-800 p-4 text-white text-center">
            <p>&copy; Habib Mote</p>
        </footer>
    </div>
    <script>
        countPos = 0;
        $(document).ready(function(){
          $('#addPos').click(function(event){
            event.preventDefault();
            if (countPos >= 9) {
              alert("Maximum of nine position entries allowed.");
              return;
            }
            countPos++;
            $('#position_fields').append(
              `<div id="position${countPos}" class="mb-4">
                <p>Year: <input type="text" class="border p-2 w-full" name="year${countPos}" value="">
                <input type="button" class="cursor-pointer bg-gray-500 px-2" value="-" onclick="$('#position${countPos}').remove();return false;"></p>
                <textarea name="desc${countPos}" class="border p-2 w-full" rows="8" cols="80"></textarea>
              </div>`
            );
          });
        var countEdu = 0;
        $('#addEdu').click(function(event) {
          event.preventDefault();
          if (countEdu >= 9) {
            alert("Maximum of nine education entries exceeded");
            return;
          }
          countEdu++;
          $('#education_fields').append(
            '<div id="edu' + countEdu + '" class="flex justify-between items-center mb-2"> \
             <p class="w-1/3">Year: <input type="text" class="border p-2" name="edu_year' + countEdu + '" value="" /> \
             <p class="w-1/3">School: <input type="text" class="school border p-2" name="edu_school' + countEdu + '"/></p> \
             <input type="button" value="-" class="cursor-pointer px-2 bg-gray-400" onclick="$(\'#edu' + countEdu + '\').remove(); return false;"></p> \
             </div>'
          );
          $('.school').autocomplete({ source: "school.php" });
          });
        });
    </script>
</body>
</html>