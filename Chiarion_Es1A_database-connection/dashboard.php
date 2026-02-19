<?php
/**
 * Function to get database parameters of the database
 * from a specified file in order to establish the connection
 * @return mixed JSON file content with parameters
 */
function get_database_parameters(){
    $file_content = file_get_contents('../database-access.json');
    return json_decode($file_content, true);
}

/**
 * Function to verify the login of the user
 * and return to the login page if it not valid
 * @param $database_data mixed data for the connection to the database
 */
function login_user($database_data){
    /* start connection with the database */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if($connection->connect_error)
        die("Connection failed: ".$connection->connect_error);
    /* get username and password from
    the form */
    $username = $_POST["username"];
    $password = $_POST["password"];

    /* compare username and password with the database */
    $query = $connection->prepare("SELECT ID FROM utenti WHERE user = ? AND password = ?");
    $query->bind_param("ss", $username, $password);
    $query->execute();
    $result = $query->get_result();
    $connection->close(); // close the connection

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
}

/**
 * Function to return a list of cars related to user
 * @param $database_data mixed data to connect to a specified database
 * @return false|mysqli_result|void result from the query
 */
function display_cars($database_data){
    /* start connection with the database */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if($connection->connect_error)
        die("Connection failed: ".$connection->connect_error);
    /* get the list of cars in order to display them on the page */
    $query = $connection->prepare("SELECT marca,modello,cilindrata,potenza,lunghezza,larghezza FROM auto WHERE proprietario = ?");
    $query->bind_param("i", $_SESSION['ID']);
    $query->execute();
    $result = $query->get_result();

    $connection->close(); // close the connection

    return $result;
}

/**
 * Function to save car data based on metadata
 * @param $database_data mixed of the database
 * @return void
 */
function add_car($database_data){
    /* start connection with the database */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if($connection->connect_error)
        die("Connection failed: ".$connection->connect_error);

    /* check data validity
    between a certain range */
    if(!is_int($_POST['displacement']) && ($_POST['displacement']<0 || $_POST['displacement']>50000)){
        echo '<script>alert("Cilindrata inserita non valida")</script>';
        return;
    }
    if(!is_int($_POST['power']) && ($_POST['power']<0 || $_POST['power']>1500)){
        echo '<script>alert("Potenza inserita non valida")</script>';
        return;
    }
    if(!is_int($_POST['width']) && ($_POST['width']<0 || $_POST['width']>500)){
        echo '<script>alert("Larghezza inserita non valida")</script>';
        return;
    }
    if(!is_int($_POST['length']) && ($_POST['length']<0 || $_POST['length']>15000)){
        echo '<script>alert("Lunghezza inserita non valida")</script>';
        return;
    }

    /* sanitize values */
    $_POST['model'] = htmlspecialchars($_POST['model']);
    $_POST['brand'] = htmlspecialchars($_POST['brand']);
    $_POST['power'] = filter_var($_POST['power'], FILTER_SANITIZE_NUMBER_INT);
    $_POST['displacement'] = filter_var($_POST['displacement'], FILTER_SANITIZE_NUMBER_INT);
    $_POST['length'] = filter_var($_POST['length'], FILTER_SANITIZE_NUMBER_INT);
    $_POST['width'] = filter_var($_POST['width'], FILTER_SANITIZE_NUMBER_INT);

    /* make prepared query */
    $query = $connection->prepare("INSERT INTO auto (marca, modello, cilindrata, potenza, lunghezza, larghezza, proprietario) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $query->bind_param("ssiiiii", $_POST['brand'], $_POST['model'], $_POST['displacement'], $_POST['power'], $_POST['length'], $_POST['width'], $_SESSION['ID']);
    $query->execute();

    $connection->close(); // close connection
    /* reset connection and redirect to the page */
    $_POST = array();
    header('Location: dashboard.php');
    exit;
}

session_start(); // start of the session
$database_data = get_database_parameters(); //get database parameters

/* check the request method and associate it to the right c */
if($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] == 'login')
    login_user($database_data);
else if($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] == 'add_car')
    add_car($database_data);

/* if the user is not logged make him come back
to the login page */
if(!$_SESSION['logged_in'])
    header("Location: login.php");

if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'])
    $result = display_cars($database_data);

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Dashboard Cliente</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
        <div class="container">
            <div class="row mt-2">
                <div class="col-12 text-center">
                    <h2>Benvenuto, <?=$_SESSION['username']?></h2>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-10 justify-content-center">
                <form method="POST" action="dashboard.php" class="d-flex flex-column">
                    <label for="brand">Inserisci macchina:</label>
                    <input type="text" placeholder="Inserisci marca" name="brand" class="form-control">
                    <label for="brand">Inserisci modello:</label>
                    <input type="text" placeholder="Inserisci modello" name="model"  class="form-control">
                    <label for="brand">Inserisci cilindrata (cc):</label>
                    <input type="number" placeholder="Inserisci cilindrata" name="displacement"  class="form-control">
                    <label for="brand">Inserisci potenza (CV):</label>
                    <input type="number" placeholder="Inserisci potenza" name="power"  class="form-control">
                    <label for="brand">Inserisci lunghezza (cm):</label>
                    <input type="number" placeholder="Inserisci lunghezza" name="length"  class="form-control">
                    <label for="brand">Inserisci larghezza (cm):</label>
                    <input type="number" placeholder="Inserisci larghezza" name="width"  class="form-control">
                    <button type="submit" class="btn btn-primary mt-3" name="action" value="add_car">Aggiungi macchina</button>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-12 text-center">
                <button class="btn btn-danger mt-3" onclick="window.location.href='logout.php'">Logout</button>
            </div>
        </div>
        <div class="row justify-content-center mt-3">
            <div class="col-10 d-flex justify-content-center">
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
            </div>
        </div>
    </body>
</html>