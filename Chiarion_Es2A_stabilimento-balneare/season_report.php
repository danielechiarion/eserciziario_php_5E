<?php
include 'classes/Account.php';
include 'classes/Season.php';

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
 * Function to get the information about the
 * money earned
 * @param $database_data mixed data for the connection of the database
 * @return mixed result coming from the database
 */
function calculate_sellings($database_data){
    /* create the connection with the database */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if($connection->connect_error)
        die("Connection failed: ".$connection->connect_error);

    /* make the query to get the total earnings gained
    in the year and the average money earned from
    each purchase */
    $startingDate = $_POST['year'] . "-01-01";
    $endingDate = $_POST['year'] . "-12-31";
    $query = $connection->prepare("SELECT SUM(prezzo) as totale, AVG(prezzo) as media, COUNT(ID) as acquisti
                                        FROM acquisto WHERE dataInizio >= ? AND dataInizio <= ?");
    $query->bind_param("ss", $startingDate, $endingDate);
    $query->execute();

    $result = $query->get_result()->fetch_assoc();
    $connection->close();

    return $result;
}

/**
 * Return the season from the year given by the POST
 * @param $database_data mixed data of the database to make the connection
 * @return Season object season with the corresponding data
 */
function select_year($database_data){
    /* create the connection with the database */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if($connection->connect_error)
        die("Connection failed: ".$connection->connect_error);

    /* make the query to
    get the season */
    $query = $connection->prepare("SELECT anno, quantitaTeli, prezzoTeli, prezzoOmbrelloni FROM stagione
                                        WHERE anno = ?");
    $query->bind_param("i", $_POST['year']);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();

    return new Season($result['anno'], $result['quantitaTeli'], $result['prezzoOmbrelloni'], $result['prezzoTeli']);
}

session_start();

/* control if the account has the
right priviledges, otherwise
change the location */
if(!isset($_SESSION['user']) || $_SESSION['user'] == null)
    header("Location: index.php");
else if($_SESSION['user']->role == AccountType::Client)
    header("Location: dashboard_client.php");

/* this page is only available if a POST
request has been made, otherwise come back
to the dashboard of the administrator */
if($_SERVER['REQUEST_METHOD'] != "POST" || $_POST['action'] != "view_report")
    header("Location: dashboard_client.php");

$database_data = get_database_parameters();
/* get the data from the various
calculation to make the report */
$season = select_year($database_data);
$selling_result = calculate_sellings($database_data);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Report di stagione</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
        <div class="container mt-4">
            <h4 class="mb-4 text-secondary border-bottom pb-2">Riepilogo Stagione <?=$season->year?></h4>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card h-100 border-start border-primary border-4 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted uppercase small">Configurazione</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex justify-content-between mb-2">
                                    <span>📦 Numero Teli:</span>
                                    <span class="fw-bold"><?=$season->quantityTowels?></span>
                                </li>
                                <li class="d-flex justify-content-between mb-2">
                                    <span>🏖️ Prezzo Ombrelloni:</span>
                                    <span class="fw-bold"><?= number_format($season->priceUmbrella, 2, ',', '.') ?> €</span>
                                </li>
                                <li class="d-flex justify-content-between">
                                    <span>🧣 Prezzo Teli:</span>
                                    <span class="fw-bold"><?= number_format($season->priceTowels, 2, ',', '.') ?> €</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-start border-success border-4 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted uppercase small">Performance Economica</h6>
                            <div class="display-6 fw-bold text-success mb-1">
                                <?= number_format($selling_result['totale'], 2, ',', '.') ?> €
                            </div>
                            <p class="text-muted small">Totale Incassato</p>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span>Media/Acquisto:</span>
                                <span class="fw-bold text-dark"><?= number_format($selling_result['media'], 2, ',', '.') ?> €</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100 border-start border-info border-4 shadow-sm">
                        <div class="card-body d-flex flex-column justify-content-center text-center">
                            <h6 class="card-subtitle mb-3 text-muted uppercase small">Volume Transazioni</h6>
                            <div class="h1 fw-bold mb-0"><?=$selling_result['acquisti']?></div>
                            <div class="text-muted">Prenotazioni Totali</div>
                            <div class="mt-auto pt-3">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: 75%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-3 mt-4">
                <div class="col-12 d-flex justify-content-center align-items-center">
                    <a class="btn btn-primary" href="dashboard_admin.php">Torna indietro</a>
                </div>
            </div>
        </div>
    </body>
</html>
