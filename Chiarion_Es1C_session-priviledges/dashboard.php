<?php
/**
 * Function to get database parameters from a JSON file
 * @return mixed JSON file content with parameters
 */
function get_database_parameters(){
    $file_content = file_get_contents('../database-access.json');
    return json_decode($file_content, true);
}

/**
 * Function to verify user login and return success/failure via JSON
 * @param $database_data mixed Data for database connection
 */
function login_user($database_data){
    /* Establish connection with the database */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if($connection->connect_error)
        die("Connection failed: ".$connection->connect_error);

    /* Get username and password from POST request */
    $username = $_POST["username"];
    $password = $_POST["password"];

    /* Compare credentials with database records */
    $query = $connection->prepare("SELECT ID,role FROM utenti WHERE user = ? AND password = ?");
    $query->bind_param("ss", $username, $password);
    $query->execute();
    $result = $query->get_result();
    $connection->close();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        /* Update session variables on success */
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['ID'] = $row['ID'];
        $_SESSION['admin'] = $row['role'] != null && $row['role'] == 'admin';
        $_SESSION['error_message'] = null;

        echo json_encode(['success' => true]);
        exit;
    } else {
        $_SESSION['error_message'] = "User not found!";
        echo json_encode(['success' => false]);
        exit;
    }
}

/**
 * Function to retrieve the list of cars associated with the user as an array
 * @param $database_data mixed Database connection data
 * @return array List of cars
 */
function display_cars($database_data){
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);

    $query = $connection->prepare("SELECT ID,marca,modello,cilindrata,potenza,lunghezza,larghezza FROM auto WHERE proprietario = ?");
    $query->bind_param("i", $_SESSION['ID']);
    $query->execute();
    $res_obj = $query->get_result();

    /* Fetch all rows into an array to avoid pointer issues during concurrent operations */
    $data = [];
    while($row = $res_obj->fetch_assoc()){
        $data[] = $row;
    }

    $connection->close();
    return $data;
}

/**
 * Function to add a new car to the database
 * @param $database_data mixed Database parameters
 */
function add_car($database_data){
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);

    /* Sanitize and filter input values */
    $brand = htmlspecialchars($_POST['brand']);
    $model = htmlspecialchars($_POST['model']);
    $displacement = filter_var($_POST['displacement'], FILTER_SANITIZE_NUMBER_INT);
    $power = filter_var($_POST['power'], FILTER_SANITIZE_NUMBER_INT);
    $length = filter_var($_POST['length'], FILTER_SANITIZE_NUMBER_INT);
    $width = filter_var($_POST['width'], FILTER_SANITIZE_NUMBER_INT);

    /* Execute prepared statement for insertion */
    $query = $connection->prepare("INSERT INTO auto (marca, modello, cilindrata, potenza, lunghezza, larghezza, proprietario) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $query->bind_param("ssiiiii", $brand, $model, $displacement, $power, $length, $width, $_SESSION['ID']);
    $query->execute();

    $connection->close();
    header('Location: dashboard.php');
    exit;
}

/**
 * Function to update car data in the database
 * @param $database_data mixed Database parameters
 * @param $previousCarResults array Current list of cars to fallback on missing data
 */
function change_car($database_data, $previousCarResults){
    $idx = $_POST['index'];

    /* Fallback to existing values if new inputs are empty or invalid */
    $marca = !empty($_POST['marca']) ? $_POST['marca'] : $previousCarResults[$idx]['marca'];
    $modello = !empty($_POST['modello']) ? $_POST['modello'] : $previousCarResults[$idx]['modello'];
    $cilindrata = ($_POST['cilindrata'] >= 0) ? $_POST['cilindrata'] : $previousCarResults[$idx]['cilindrata'];
    $potenza = ($_POST['potenza'] >= 0) ? $_POST['potenza'] : $previousCarResults[$idx]['potenza'];
    $lunghezza = ($_POST['lunghezza'] >= 0) ? $_POST['lunghezza'] : $previousCarResults[$idx]['lunghezza'];
    $larghezza = ($_POST['larghezza'] >= 0) ? $_POST['larghezza'] : $previousCarResults[$idx]['larghezza'];

    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);

    /* Update all fields for the specified car ID */
    $query = $connection->prepare("UPDATE auto 
                SET marca = ?, modello = ?, cilindrata = ?, potenza = ?, lunghezza = ?, larghezza = ? 
                WHERE ID = ?");
    $query->bind_param("ssiiiii", $marca, $modello, $cilindrata, $potenza, $lunghezza, $larghezza, $previousCarResults[$idx]['ID']);
    $query->execute();

    $connection->close();
}

/**
 * Function to remove a car from the database
 * @param $database_data mixed Database parameters
 * @param $previousCarResults array Current list of cars
 */
function delete_car($database_data, $previousCarResults){
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);

    $query = $connection->prepare("DELETE FROM auto WHERE ID = ?");
    $query->bind_param("i", $previousCarResults[$_POST['index']]['ID']);
    $query->execute();

    $connection->close();
}

/* Initialization */
session_start();
$database_data = get_database_parameters();

/* Handle authentication before view logic */
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'login') {
    login_user($database_data);
}

/* Redirect unauthorized users */
if(!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']){
    header("Location: login.php");
    exit;
}

/* Load car data for the current session as an array */
$result = display_cars($database_data);

/* Route POST actions to corresponding functions */
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])){
    if($_POST['action'] == 'add_car') add_car($database_data);
    else if($_POST['action'] == 'change_car') change_car($database_data, $result);
    else if($_POST['action'] == 'delete_car') delete_car($database_data, $result);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
<div class="container">
    <div class="row mt-2 text-center">
        <div class="col-12">
            <h2>Welcome, <?=$_SESSION['username']?></h2>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-10">
            <form method="POST" action="dashboard.php" class="d-flex flex-column">
                <label for="brand" class="mb-3">Inserisci dettagli della macchina:</label>
                <input type="text" placeholder="Marca" name="brand" class="form-control mb-1">
                <input type="text" placeholder="Modello" name="model"  class="form-control mb-1">
                <input type="number" placeholder="Cilindrata (cc)" name="displacement"  class="form-control mb-1">
                <input type="number" placeholder="Potenza (CV)" name="power"  class="form-control mb-1">
                <input type="number" placeholder="Lunghezza (cm)" name="length"  class="form-control mb-1">
                <input type="number" placeholder="Larghezza (cm)" name="width"  class="form-control mb-1">
                <button type="submit" class="btn btn-primary mt-3" name="action" value="add_car">Add car</button>
            </form>
            <div class="d-flex justify-content-center mt-3">
                <button class="btn btn-danger" onclick="window.location.href='logout.php'">Logout</button>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mt-3">
        <div class="col-10">
            <?php if(empty($result)): ?>
                <div class="alert alert-danger text-center">
                    <strong>No cars found in the database.</strong>
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
                        <?php if($_SESSION['admin']) echo "<th>Actions</th>"; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($result as $index => $row): ?>
                        <tr class="tr">
                            <td class="view-fields"><?=$row['marca']?></td>
                            <td class="view-fields"><?=$row['modello']?></td>
                            <td class="view-fields"><?=$row['cilindrata']?>cc</td>
                            <td class="view-fields"><?=$row['potenza']?>CV</td>
                            <td class="view-fields"><?=$row['lunghezza']?>cm</td>
                            <td class="view-fields"><?=$row['larghezza']?>cm</td>

                            <td class="editable-fields d-none"><input type="text" class="form-control" name="marca" value="<?=$row['marca']?>"></td>
                            <td class="editable-fields d-none"><input type="text" class="form-control" name="modello" value="<?=$row['modello']?>"></td>
                            <td class="editable-fields d-none"><input type="number" class="form-control" name="cilindrata" value="<?=$row['cilindrata']?>"></td>
                            <td class="editable-fields d-none"><input type="number" class="form-control" name="potenza" value="<?=$row['potenza']?>"></td>
                            <td class="editable-fields d-none"><input type="number" class="form-control" name="lunghezza" value="<?=$row['lunghezza']?>"></td>
                            <td class="editable-fields d-none"><input type="text" class="form-control" name="larghezza" value="<?=$row['larghezza']?>"></td>

                            <?php if($_SESSION['admin']): ?>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <button class="btn btn-warning btn-sm change-vehicle">Edit</button>
                                        <button class="btn btn-danger btn-sm delete-vehicle">Delete</button>
                                        <button class="btn btn-success btn-sm confirm-change-vehicle d-none">Save</button>
                                        <button class="btn btn-secondary btn-sm cancel-change-vehicle d-none">Cancel</button>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="research-table.js"></script>
<script src="car_operations.js"></script>
</body>
</html>