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
    $query = $connection->prepare("SELECT ID,role FROM utenti WHERE user = ? AND password = ?");
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
        $_SESSION['admin'] = $row['role'] != null && $row['role'] == 'admin';

        $_SESSION['error_message'] = null; // remove possible error messages

        echo json_encode(['success' => true]); //return success to ajax
        exit;
    } else {
        $_SESSION['error_message'] = "User not found!";
        echo json_encode(['success' => false]); //return success to ajax
        exit;
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
    $query = $connection->prepare("SELECT ID,marca,modello,cilindrata,potenza,lunghezza,larghezza FROM auto WHERE proprietario = ?");
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

/**
 * Function to research a car and change the view of the table
 * @param $database_data
 * @return void
 */
function research_car($database_data){
    /* establish connection */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if($connection->connect_error){
        die(json_encode(['error' => 'Connection failed: ' . $connection->connect_error]));
    }

    /* prepare the query before executing it */
    $sql = "SELECT marca,modello,cilindrata,potenza,lunghezza,larghezza FROM auto WHERE proprietario = ? ";
    $types = "i";
    $userId = $_SESSION['ID'];
    $searchValues = array();

    /* add for each key the condition to search similar values */
    foreach($_POST as $postKey => $postValue){
        if($postKey == 'action') // action doesn't have to be considered
            continue;

        $sql .= "AND $postKey LIKE ? ";
        $types .= "s";

        /* add wildcards to the search value */
        $searchValues[$postKey] = "%{$postValue}%";
    }

    /* execute the query and save the result obtained */
    $query = $connection->prepare($sql);
    if (!$query) {
        die(json_encode(['error' => 'Prepare failed: ' . $connection->error]));
    }

    /* bind parameters properly */
    $bindParams = array($types, &$userId);
    foreach($searchValues as &$value){
        $bindParams[] = &$value;
    }

    call_user_func_array(array($query, 'bind_param'), $bindParams);
    $query->execute();
    $result = $query->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $query->close();
    $connection->close();

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Function to change the car in the database,
 * so as to update also the page
 * @param $database_data mixed data for the connection to the database
 * @param $previousCarResults mixed previous data of the cars
 * @return void
 */
function change_car($database_data, $previousCarResults){
    /* check the validity of the data.
    If they're null or not valid, replace them with the
    previous ones */
    if($_POST['marca'] == "")
        $_POST['marca'] = $previousCarResults[$_POST['index']]['marca'];
    if($_POST['modello'] == "")
        $_POST['modello'] = $previousCarResults[$_POST['index']]['modello'];

    if($_POST['cilindrata']<0 || $_POST['cilindrata']>50000)
        $_POST['cilindrata'] = $previousCarResults[$_POST['index']]['cilindrata'];
    else
        $_POST['cilindrata'] = filter_var($_POST['cilindrata'], FILTER_SANITIZE_NUMBER_INT);

    if($_POST['potenza']<0 || $_POST['potenza']>1500)
        $_POST['potenza'] = $previousCarResults[$_POST['index']]['potenza'];
    else
        $_POST['potenza'] = filter_var($_POST['potenza'], FILTER_SANITIZE_NUMBER_INT);

    if($_POST['larghezza']<0 || $_POST['larghezza']>500)
        $_POST['larghezza'] = $previousCarResults[$_POST['index']]['larghezza'];
    else
        $_POST['larghezza'] = filter_var($_POST['larghezza'], FILTER_SANITIZE_NUMBER_INT);

    if($_POST['lunghezza']<0 || $_POST['lunghezza']>15000)
        $_POST['lunghezza'] = $previousCarResults[$_POST['index']]['lunghezza'];
    else
        $_POST['lunghezza'] = filter_var($_POST['lunghezza'], FILTER_SANITIZE_NUMBER_INT);

    /* establish connection */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if($connection->connect_error){
        die(json_encode(['error' => 'Connection failed: ' . $connection->connect_error]));
    }

    /* update all the fields of the query
    that can be changed with the new values */
    $query = $connection->prepare("UPDATE auto
                SET marca = ?, modello = ? cilindrata = ?, potenza = ?, lunghezza = ?, larghezza = ?,
                WHERE ID = ?");
    $query->bind_param("ssiiiii", $_POST['marca'], $_POST['modello'], $_POST['cilindrata'],
                $_POST['potenza'], $_POST['lunghezza'], $_POST['larghezza'],
                $previousCarResults[$_POST['index']['ID']]);
    $query->execute();
    $connection->close(); // close connection
}

/**
 * Function to delete the car from the list
 * @param $database_data mixed data for the connection to the database
 * @param $previousCarResults mixed previous car data
 * @return void
 */
function delete_car($database_data, $previousCarResults){
    /* establish connection */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if($connection->connect_error){
        die(json_encode(['error' => 'Connection failed: ' . $connection->connect_error]));
    }

    /* then delete the car with that specified ID */
    $query = $connection->prepare("DELETE FROM auto WHERE ID = ?");
    $query->bind_param("i", $previousCarResults[$_POST['index']]['ID']);
    $query->execute();
    $connection->close();
}

session_start(); // start of the session
$database_data = get_database_parameters(); //get database parameters

/* Process login FIRST before checking if user is logged in */
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'login') {
    login_user($database_data);
}

/* if the user is not logged make him come back
to the login page */
if(!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']){
    header("Location: login.php");
    exit;
}

if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'])
    $result = display_cars($database_data);

/* check the request method and associate it to the right function */
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])){
    if($_POST['action'] == 'add_car')
        add_car($database_data);
    else if($_POST['action'] == 'search_car')
        research_car($database_data);
    else if($_POST['action'] == 'change_car')
        change_car($database_data, $result);
    else if($_POST['action'] == 'delete_car')
        delete_car($database_data, $result);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
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
            <table class="table table-striped mt-3" style="table-layout: fixed;">
                <thead>
                <tr>
                    <th class="table-search-column" data-field="marca">
                        <div class="d-flex justify-content-between">
                            Marca
                            <button class="table-search-btn" style="padding: 0; border: none; background: none; cursor: pointer; font-size: 1rem;">🔍</button>
                        </div>
                        <input type="text" class="table-search-input" style="display:none;" placeholder="Cerca...">
                    </th>
                    <th class="table-search-column" data-field="modello">
                        <div class="d-flex justify-content-between">
                            Modello
                            <button class="table-search-btn" style="padding: 0; border: none; background: none; cursor: pointer; font-size: 1rem;">🔍</button>
                        </div>
                        <input type="text" class="table-search-input" style="display:none;" placeholder="Cerca...">
                    </th>
                    <th class="table-search-column" data-field="cilindrata">
                        <div class="d-flex justify-content-between">
                            Cilindrata
                            <button class="table-search-btn" style="padding: 0; border: none; background: none; cursor: pointer; font-size: 1rem;">🔍</button>
                        </div>
                        <input type="number" class="table-search-input" style="display:none;">
                    </th>
                    <th class="table-search-column" data-field="potenza">
                        <div class="d-flex justify-content-between">
                            Potenza
                            <button class="table-search-btn" style="padding: 0; border: none; background: none; cursor: pointer; font-size: 1rem;">🔍</button>
                        </div>
                        <input type="number" class="table-search-input" style="display:none;">
                    </th>
                    <th class="table-search-column" data-field="lunghezza">
                        <div class="d-flex justify-content-between">
                            Lunghezza
                            <button class="table-search-btn" style="padding: 0; border: none; background: none; cursor: pointer; font-size: 1rem;">🔍</button>
                        </div>
                        <input type="number" class="table-search-input" style="display:none;">
                    </th>
                    <th class="table-search-column" data-field="larghezza">
                        <div class="d-flex justify-content-between">
                            Larghezza
                            <button class="table-search-btn" style="padding: 0; border: none; background: none; cursor: pointer; font-size: 1rem;">🔍</button>
                        </div>
                        <input type="number" class="table-search-input" style="display:none;">
                    </th>
                    <?php
                        if($_SESSION['admin']):
                            echo "<th>Azioni</th>";
                        endif;
                    ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach($result as $row){ ?>
                    <tr>
                        <td class="view-fields"><?=$row['marca']?></td>
                        <td class="view-fields"><?=$row['modello']?></td>
                        <td class="view-fields"><?=$row['cilindrata']?>cc</td>
                        <td class="view-fields"><?=$row['potenza']?>CV</td>
                        <td class="view-fields"><?=$row['lunghezza']?>cm</td>
                        <td class="view-fields"><?=$row['larghezza']?>cm</td>

                        <td class="editable-fields d-none">
                            <input type="text" class="form-control" name="marca" value="<?=$row['marca']?>">
                        </td>
                        <td class="editable-fields d-none">
                            <input type="text" class="form-control" name="modello" value="<?=$row['modello']?>">
                        </td>
                        <td class="editable-fields d-none">
                            <input type="number" class="form-control" name="cilindrata" value="<?=$row['cilindrata']?>"> cc
                        </td>
                        <td class="editable-fields d-none">
                            <input type="number" class="form-control" name="potenza" value="<?=$row['potenza']?>"> CV
                        </td>
                        <td class="editable-fields d-none">
                            <input type="number" class="form-control" name="lunghezza" value="<?=$row['lunghezza']?>"> cm
                        </td>
                        <td class="editable-fields d-none">
                            <input type="text" class="form-control" name="larghezza" value="<?=$row['larghezza']?>"> cm
                        </td>
                        <?php if($_SESSION['admin']){ ?>
                        <td>
                            <div class="d-flex justify content-center">
                                <button class="btn btn-warning change-vehicle">Modifica veicolo</button>
                                <button class="btn btn-danger delete-vehicle">Elimina veicolo</button>
                                <button class="btn btn-success confirm-change-vehicle d-none">Salva modifiche</button>
                                <button class="btn btn-danger cancel-change-vehicle d-none">Annulla modifiche</button>
                            </div>
                        </td>
                        <?php }?>
                        ?>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script src="research-table.js"></script>
<script src="car_operations.js"></script>
</body>
</html>