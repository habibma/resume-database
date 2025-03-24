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

$stmt = $pdo->prepare('SELECT * FROM profile WHERE profile_id = :pid AND user_id = :uid');
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

$stmt = $pdo->prepare("SELECT * FROM position WHERE profile_id = :pid ORDER BY rank");
$stmt->execute([':pid' => $_GET['profile_id']]);
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT Education.year, Institution.name FROM Education JOIN Institution ON Education.institution_id = Institution.institution_id WHERE profile_id = :pid ORDER BY rank");
$stmt->execute([':pid' => $_GET['profile_id']]);
$institions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['headline']) || empty($_POST['summary']) || empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email'])){
        $_SESSION['error'] = "All fields are required!";
        header("Location: add.php");
        return;
    }
    if (strpos($_POST['email'], '@') === false){
        $_SESSION['error'] = "Email address must contaion @";
        header("Location: edit.php?profile_id=" . $_POST['profile_id']);
        return;
    }
    $stmt = $pdo->prepare('UPDATE profile SET first_name = :fn, last_name = :ln, email = :em, headline = :hl, summary = :sm WHERE profile_id = :pid AND user_id = :uid');
    $stmt->execute(array(
        ':uid' => $_SESSION['user_id'],
        ':pid' => $_POST['profile_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':hl' => $_POST['headline'],
        ':sm' => $_POST['summary']
    ));

    // Delete old positions
    $stmt = $pdo->prepare("DELETE FROM Position WHERE profile_id=:pid");
    $stmt->execute([':pid' => $_POST['profile_id']]);

    // Insert new positions
    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['year' . $i]) || !isset($_POST['desc' . $i])) continue;

        $year = $_POST['year' . $i];
        $desc = $_POST['desc' . $i];

        if (empty($year) || empty($desc)) {
            $_SESSION['error'] = "All fields are required";
            header("Location: edit.php?profile_id=" . $_POST['profile_id']);
            exit();
        }
        if (!is_numeric($year)) {
            $_SESSION['error'] = "Year must be numeric";
            header("Location: edit.php?profile_id=" . $_POST['profile_id']);
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO Position (profile_id, rank, year, description) 
                               VALUES (:pid, :rank, :year, :desc)");
        $stmt->execute([
            ':pid' => $_POST['profile_id'],
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc
        ]);
        $rank++;
    }

    // Delete old institutions
    $stmt = $pdo->prepare("DELETE FROM Education WHERE profile_id=:pid");
    $stmt->execute([':pid' => $_POST['profile_id']]);  
    // Insert new institutions
    $rank = 1;
    for ($i = 1; $i <= 9; $i++) {
        if (!isset($_POST['edu_year' . $i]) || !isset($_POST['edu_school' . $i])) continue;
        $year = $_POST['edu_year' . $i];
        $schoolName = $_POST['edu_school' . $i];
        if (empty($year) || empty($schoolName)) {
            $_SESSION['error'] = "All fields are required";
            header("Location: edit.php?profile_id=" . $_POST['profile_id']);
            exit();
        }
        if (!is_numeric($year)) {
            $_SESSION['error'] = "Year must be numeric";
            header("Location: edit.php?profile_id=" . $_POST['profile_id']);
            exit();
        }
        // Check if institution exists
        $stmt = $pdo->prepare("SELECT institution_id FROM Institution WHERE name = :name");
        $stmt->execute(array(':name' => $schoolName));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $institution_id = $row['institution_id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO Institution (name) VALUES (:name)");
            $stmt->execute(array(':name' => $schoolName));
            $institution_id = $pdo->lastInsertId();
        }
        // Insert into Education table
        $stmt = $pdo->prepare("INSERT INTO Education (profile_id, institution_id, rank, year) VALUES (:pid, :iid, :rank, :year)");
        $stmt->execute(array(
            ':pid' => $_POST['profile_id'],
            ':iid' => $institution_id,
            ':rank' => $rank,
            ':year' => $year
        ));
        $rank++;
    }
    $_SESSION['success'] = "Profile " .$_POST['profile_id'] ." Edited!!";
    header("Location: index.php");
    return;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Habib Mote - Edit Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css"></head>
</head>
<body>
    <div class="min-h-screen">
        <header class="bg-gray-800 text-white py-4 px-8 flex justify-between items-center">
            <h1 class="text-xl font-bold">Edit Profile</h1>
            <a href="index.php" class="text-white underline">Back to Home</a>
        </header>
        <main class="p-4 flex justify-center items-center h-full">
            <div class="container bg-white p-8 rounded shadow-md w-1/2">
                <h1 class="text-xl font-bold my-4">Edit Profile</h1>
                <?php
                    // Show error message
                    if (isset($_SESSION['error'])) {
                        echo '<p style="color:red">'.htmlentities($_SESSION['error'])."</p>\n";
                        unset($_SESSION['error']);
                    }
                ?>
                <form method="post">
                <input type="hidden" name="profile_id" value="<?= htmlentities($_GET['profile_id']) ?>">
                <fieldset class="mb-4 flex">
                        <div class="w-1/2 pr-2">
                            <label class="block" for="first_name">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="border p-2 w-full" value="<?= htmlentities($profile['first_name']) ?>">
                        </div>
                        <div class="w-1/2 pr-2">
                            <label class="block" for="last_name">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="border p-2 w-full" value="<?= htmlentities($profile['last_name']) ?>">
                        </div>
                    </fieldset>
                    <label>Email: <input type="text" name="email" class="border p-2 w-full" value="<?= htmlentities($profile['email']) ?>"></label>
                    <label>Headline: <input type="text" name="headline" class="border p-2 w-full" value="<?= htmlentities($profile['headline']) ?>"></label>
                    <label>Summary: <textarea name="summary"class="border p-2 w-full"><?= htmlentities($profile['summary']) ?></textarea></label>
                    <fieldset class="mb-4">
                        <p>Position: <input type="button" id="addPos" class="cursor-pointer px-2 bg-gray-500" value="+"></p>
                        <div id="position_fields">
                            <?php
                            $countPos = 0;
                            foreach ($positions as $pos) {
                                $countPos++;
                                echo '<div id="position'.$countPos.'">
                                        <p>Year: <input type="text" class="border p-2 w-full" name="year'.$countPos.'" value="'.htmlentities($pos['year']).'">
                                        <input type="button" class="cursor-pointer px-2 bg-gray-400" value="-" onclick="$(\'#position'.$countPos.'\').remove();return false;"></p>
                                        <textarea class="border p-2 w-full" name="desc'.$countPos.'" rows="8" cols="80">'.htmlentities($pos['description']).'</textarea>
                                      </div>';
                                }
                            ?>
                        </div>
                    </fieldset>
                    <fieldset class="mb-4">
                        <p>Education: <input type="button" id="addEdu" class="cursor-pointer px-2 bg-gray-500" value="+"></p>
                        <div id="education_fields">
                            <?php
                            $countEdu = 0;
                            foreach ($institions as $edu) {
                                $countEdu++;
                                echo '<div id="education'.$countEdu.'">
                                        <p>Year: <input type="text" class="border p-2 w-full" name="edu_year'.$countEdu.'" value="'.htmlentities($edu['year']).'">
                                        <input type="button" class="cursor-pointer px-2 bg-gray-400" value="-" onclick="$(\'#education'.$countEdu.'\').remove();return false;"></p>
                                        <p>School: <input type="text" class="border p-2 w-full" name="edu_school'.$countEdu.'" value="'.htmlentities($edu['name']).'"></p>
                                      </div>';
                                }
                            ?>
                        </div>
                    </fieldset>
                    <p><input type="submit" value="Save Change" class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-500">
                    <a class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-500" href="index.php">Cancel</a></p>
                </form>
            </div>
        </main>
        <footer class="bg-gray-800 p-4 text-white text-center">
            <p>&copy; Habib Mote</p>
        </footer>
    </div>
    <script>
    countPos = <?= $countPos ?>;
    $(document).ready(function(){
      $('#addPos').click(function(event){
        event.preventDefault();
        if (countPos >= 9) {
          alert("Maximum of nine position entries allowed.");
          return;
        }
        countPos++;
        $('#position_fields').append(
          `<div id="position${countPos}">
            <p>Year: <input type="text" class="border p-2 w-full" name="year${countPos}" value="">
            <input type="button" class="cursor-pointer px-2 bg-gray-400" value="-" onclick="$('#position${countPos}').remove();return false;"></p>
            <textarea class="border p-2 w-full" name="desc${countPos}" rows="8" cols="80"></textarea>
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