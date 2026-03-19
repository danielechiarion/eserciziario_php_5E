<?php
include 'classes/Account.php';

session_start();

/**
 * Function to get database parameters of the database
 * from a specified file in order to establish the connection
 * @return mixed JSON file content with parameters
 */
function get_database_parameters(){
    $file_content = file_get_contents('../database-access.json');
    return json_decode($file_content, true);
}

/* if the user wants to sign up but is logged,
first logout and then sign up again */
if(isset($_SESSION['user']) && $_SESSION['user']!=null)
    header("Location: logout.php");

$database_data = get_database_parameters();

/* get the method to sign up */
if($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action']=='sign-up'){
    /* start connection with the database */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if($connection->connect_error)
        die("Connection failed: ".$connection->connect_error);

    /* preare the query to insert the account */
    $ROLE = AccountType::Client->value;
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $query = $connection->prepare("INSERT INTO account (username, cliente, password, role) VALUES (?, ?, ?, ?)");
    $query->bind_param("ssss", $_POST['username'], $_POST['CF'], $password, $ROLE);
    $query->execute();
    $query = $connection->prepare("INSERT INTO persona (CF, nome, cognome) VALUES (?, ?, ?)");
    $query->bind_param("sss", $_POST['CF'], $_POST['name'], $_POST['surname']);
    $query->execute();

    $connection->close();
    header("Location: index.php"); // bring back to the login page
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registrati</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-12 mb-4 text-center">
            <h1>Sign Up</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <form method="POST" action="sign-up.php" class="d-flex flex-column">
                <input class="form-control item-space" type="text" placeholder="Inserisci username..." name="username" required>
                <input class="form-control item-space" type="password" name="password" required>
                <input class="form-control item-space" type="text" name="CF" placeholder="Inserisci CF..." minlength="16" maxlength="16" required>
                <input class="form-control item-space" type="text" name="name" placeholder="Inserisci nome..." required>
                <input class="form-control item-space" type="text" name="surname" placeholder="Inserisci cognome..." required>
                <button class="btn btn-primary item-space" type="submit" name="action" value="sign-up">Registrati</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>