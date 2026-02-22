<?php
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
if(array_key_exists('logged_in', $_SESSION) && $_SESSION['logged_in'])
    header("Location: logout.php");

$database_data = get_database_parameters();

/* get the method to sign up */
if($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action']=='sign-up'){
    /* start connection with the database */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if($connection->connect_error)
        die("Connection failed: ".$connection->connect_error);

    /* preare the query to insert the account */
    $query = $connection->prepare("INSERT INTO utenti (user, password) VALUES (?, ?)");
    $query->bind_param("ss", $_POST['username'], $_POST['password']);
    $query->execute();

    $connection->close();
    header("Location: login.php"); // bring back to login page
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
                        <button class="btn btn-primary item-space" type="submit" name="action" value="sign-up">Registrati</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>