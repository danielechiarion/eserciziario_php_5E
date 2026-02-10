<?php
session_start(); // start of the session
$connection = new mysqli("192.168.60.144", "daniele_chiarion", "", "daniele_chiarion_auto");

if($connection->connect_error)
    die("Connection failed: ".$connection->connect_error);

/* get username and password from the */
if($_SERVER["REQUEST_METHOD"] == "POST"){
    /* get username and password from
    the form */
    $username = $_POST["username"];
    $password = $_POST["password"];

    /* compare username and password with the database */
    $query = $connection->prepare("SELECT ID FROM utenti WHERE user = ? AND password = ?");
    $query->bind_param("ss", $username, $password);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        /* update session variables */
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['ID'] = $row['ID'];

        $_SESSION['error_message'] = null; // remove possible error messages
    } else {
        $_SESSION['error_message'] = "User not found!";
        header("Location: login.php");
    }

    /* get the list of cars in order to display them on the page */
    $query = $connection->prepare("SELECT marca,modello,cilindrata,potenza,lunghezza,larghezza FROM auto WHERE proprietario = ?");
    $query->bind_param("i", $_SESSION['ID']);
    $query->execute();
    $result = $query->get_result();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Dashboard Cliente</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php if($result->num_rows === 0){ ?>
        <div class="alert alert-danger mt-3 d-flex justify-content-center px-3">
            <strong>Nessuna macchina inserita</strong>
        </div>
        <?php } else {} ?>
    </body>
</html>
<?php $connection->close(); ?>