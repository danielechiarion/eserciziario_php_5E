<?php
function get_database_parameters(){
    $file_content = file_get_contents('../database-access.json');
    return json_decode($file_content, true);
}

session_start(); // start of the session
$database_data = get_database_parameters(); //get database parameters
$connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);

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
        exit(1);
    }

    /* get the list of cars in order to display them on the page */
    $query = $connection->prepare("SELECT marca,modello,cilindrata,potenza,lunghezza,larghezza FROM auto WHERE proprietario = ?");
    $query->bind_param("i", $_SESSION['ID']);
    $query->execute();
    $result = $query->get_result();
}else{
    header("Location: login.php");
    exit(1);
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
        <?php if(!isset($result) || $result->num_rows == 0): ?>
        <div class="alert alert-danger mt-3 d-flex justify-content-center px-3">
            <strong>Nessuna macchina inserita</strong>
        </div>
        <?php else: ?>
                <table class="table table-striped mt-3">
                    <thead>
                        <tr>
                            <th>Marca</th>
                            <th>Modello</th>
                            <th>Cilindrata</th>
                            <th>Potenza</th>
                            <th>Lunghezza</th>
                            <th>Larghezza</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($result as $row){ ?>
                            <tr>
                                <td><?=$row['marca']?></td>
                                <td><?=$row['modello']?></td>
                                <td><?=$row['cilindrata']?>cc</td>
                                <td><?=$row['potenza']?>CV</td>
                                <td><?=$row['lunghezza']?>cm</td>
                                <td><?=$row['larghezza']?>cm</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
        <?php endif; ?>
    </body>
</html>
<?php $connection->close(); ?>