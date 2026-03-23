<?php
include 'classes/Season.php';
include 'classes/Account.php';

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
 * Function to get the seasons from the database
 * so as to display them in the page
 * @return Season[] list of seasons
 */
function get_seasons($database_data){
    /* create the connection with the database */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if($connection->connect_error)
        die("Connection failed: ".$connection->connect_error);

    /* get all the seasons joined with the
    umbrellas */
    $query = $connection->prepare("SELECT anno,quantitaTeli,prezzoTeli,prezzoOmbrelloni,ombrellone
                                    FROM stagione JOIN stagione_ombrellone ON stagione.anno = stagione_ombrellone.stagione
                                    ORDER BY anno DESC");
    $query->execute();
    $result = $query->get_result();
    $connection->close();

    /* then create the objects of the season and
    add every umbrella */
    $seasonList = [];
    for($i=0;$i<$result->num_rows;$i++){
        $row = $result->fetch_assoc();
        /* if there is a new season to add,
         add it to the list */
        if($i==0 || end($seasonList)->year != $row['anno'])
            $seasonList[] = new Season($row['anno'], $row['quantitaTeli'], $row['prezzoOmbrelloni'], $row['prezzoTeli']);

        /* then add anyway at the last
        element the new umbrella */
        end($seasonList)->addUmbrella($row['ombrellone']);
    }

    return $seasonList; // return the list of seasons
}

/**
 * Check if the beach loungers table
 * has been populated
 * @param $database_data
 * @return int number of the loungers found in the database
 * FALSE when it is not
 */
function check_beach_lounger($database_data){
    /* create the connection with the database */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if($connection->connect_error)
        die("Connection failed: ".$connection->connect_error);

    /* get the results and check the number
    of rows available */
    $query = $connection->prepare("SELECT COUNT(ID) as totale FROM lettino");
    $query->execute();
    $result = $query->get_result();

    $_SESSION['loungers_number'] = $result->fetch_assoc()['totale'];

    return $_SESSION['loungers_number'];
}

/**
 * Function to update beach lounger adding
 * them to the database
 * @param $database_data mixed for the connection to the database
 * @param $currentBeachLoungers int number of beach loungers already present
 * @return void
 */
function update_beach_loungers($database_data, &$currentBeachLoungers){
    /* create the connection with the database */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if($connection->connect_error)
        die("Connection failed: ".$connection->connect_error);

    /* find the difference between the previous number
    of beach loungers and the one given and
    insert them into the database */
    for($i=0;$i<$_POST['beach_loungers']-$currentBeachLoungers;$i++)
        $connection->query("INSERT INTO lettino () VALUES ()");

    $currentBeachLoungers = $_POST['beach_loungers']; // update also the beach lounger counter

    $connection->close();
}

/**
 * Function to save the season into the database
 * updating the related tables
 * @param $database_data mixed data for the connection to the database
 * @return void
 */
function save_season($database_data){
    /* create the connection with the database */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if ($connection->connect_error)
        die("Connection failed: " . $connection->connect_error);

    /* first save the season inside the related table */
    $year = date("Y");
    $query = $connection->prepare("INSERT IGNORE INTO stagione(anno, quantitaTeli, prezzoTeli, prezzoOmbrelloni) 
                                            VALUES (?, ?, ?, ?)");
    $query->bind_param("iidd", $year, $_POST['number_towels'], $_POST['price_towels'], $_POST['price_umbrellas']);
    $query->execute();

    /* update the relation between
    umbrellas and season */
    for($i=1;$i<=$_POST['number_umbrellas'];$i++){
        $query = $connection->prepare("INSERT INTO stagione_ombrellone (stagione, ombrellone) VALUES (?,?)");
        $query->bind_param("ii", $year, $i);
        $query->execute();
    }

    /* add eventual umbrella if it's necessary
    and update the IDs */
    $query = $connection->prepare("SELECT COUNT(ID) as totale FROM ombrellone");
    $query->execute();
    $umbrellas = $query->get_result()->fetch_assoc()['totale'];

    for($i=0;$i<$_POST['number_umbrellas']-$umbrellas;$i++){
        $query = $connection->prepare("INSERT INTO ombrellone () VALUES ()");
        $query->execute();
    }

    $connection->close();

    /* at the end reload the page */
    header("Location: {$_SERVER['PHP_SELF']}");
}

session_start(); // start the session

/* if the user is not logged in,
come back to the login part */
if(!isset($_SESSION['user']) || $_SESSION['user'] == null)
    header("Location: index.php");
/* othewrise if it's a client move
to the client dashboard */
else if($_SESSION['user']->role == AccountType::Client)
    header("Location: dashboard_client.php");

$database_data = get_database_parameters(); // get the data from the database

$beach_loungers = check_beach_lounger($database_data); // get the beach loungers number
$seasons = get_seasons($database_data); // get also the seasons

/* control the requests coming */
if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['action'] == "update_loungers")
    update_beach_loungers($database_data, $beach_loungers);
else if($_SERVER['REQUEST_METHOD']=="POST" && $_POST['action']=='add_season')
    save_season($database_data);
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Dashboard Amministratore</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <link rel="stylesheet" type="text/css" href="style.css">
    </head>
    <body>
    <div class="container mt-3">
        <!-- Header of the page -->
        <div class="row mb-5 dashboard-header">
            <div class="col-3"></div>
            <div class="col-6 d-flex justify-content-center">
                <h2>Dashboard amministrazione</h2>
            </div>
            <div class="col-3 d-flex justify-content-center">
                <form action="logout.php" method="POST">
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </div>
        </div>

        <!-- Section of the loungers to view and add -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-center">
                <h3>Lettini</h3>
            </div>
            <?php
                /* check in this session if beach loungers
                have been added at least once, otherwise
                return a message to immediately add them */
                if($beach_loungers != 0):
            ?>
            <div class="col-6 d-flex justify-content-end align-items-center">
                <p>Hai a disposizione il seguente numero di lettini:</p>
            </div>
            <?php else: ?>
            <div class="col-6 d-flex justify-content-end align-items-center">
                <div class="alert alert-danger">
                    <p>Non hai ancora inserito lettini disponibili. Inseriscili altrimenti non è possibile prenotarli!</p>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-6 d-flex justify-content-start">
                <form action="<?=$_SERVER['PHP_SELF']?>" method="POST" class="d-flex align-items-center">
                    <input type="number" value="<?=$beach_loungers?>" min="<?=$beach_loungers?>" max="1000" name="beach_loungers" required class="me-4 form-control">
                    <button type="submit" class="btn btn-success" name="action" value="update_loungers">Conferma</button>
                </form>
            </div>
        </div>

        <!-- Section of the seasons to view -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-center">
                <h3>Stagioni</h3>
            </div>
            <?php
                /* control if the latest season has been
                added, otherwise throw a section with an alarm
                with the message to add it */
                $result = array_filter($seasons, function($item){
                   return $item->equals(new Season(date("Y"), 0, 0.0, 0.0));
                });

                if(empty($result)):
            ?>
            <div class="col-6 d-flex justify-content-center align-items-center flex-column">
                <div class="alert alert-danger">
                    Stagione corrente non inserita! Nessuno può prenotare nello stabilimento.
                </div>
                <button class="btn btn-primary" id="add_season">Inserisci stagione</button>
            </div>
            <div class="col-6 d-flex justify-content-center align-items-center flex-column d-none" id="add-season_form">
                <form action="<?=$_SERVER['PHP_SELF']?>" method="POST">
                    <label>Inserisci il numero di ombrelloni: </label>
                    <input type="number" name="number_umbrellas" min="0" max="200" class="form-control" required>
                    <label>Inserisci il numero di teli: </label>
                    <input type="number" name="number_towels" min="0" max="200" class="form-control" required>
                    <label>Inserisci il prezzo degli ombrelloni </label>
                    <div class="d-flex justify-content-center">
                        <input type="number" name="price_umbrellas" min="0" max="30" step="0.01" class="form-control" required> €
                    </div>
                    <label>Inserisci il prezzo dei teli: </label>
                    <div class="d-flex justify-content-center">
                        <input type="number" name="price_towels" min="0" max="10" step="0.01" class="form-control" required> €
                    </div>

                    <button type="submit" class="btn btn-success mt-3" name="action" value="add_season">Aggiungi</button>
                </form>
            </div>
        <?php endif; ?>
        <?php
            /* then check if the season list
            has some values, or it's completely empty.
            In that case don't display anything */
            if(!empty($seasons)):
        ?>
            <div class="col-12 d-flex justify-content-center">
                <table class="table table-striped text-center align-middle">
                    <thead>
                        <tr>
                            <th>Anno</th>
                            <th>Numero teli</th>
                            <th>Prezzo ombrelloni</th>
                            <th>Prezzo teli</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        /* then display the seasons */
                        foreach($seasons as $season){
                    ?>
                            <tr>
                                <td class="season-year"><?=$season->year?></td>
                                <td class="season-quantity-towels"><?=$season->quantityTowels?></td>
                                <td class="season-price-umbrella"><?=number_format($season->priceUmbrella, 2, ',', '.')?> €</td>
                                <td class="season-price-towels"><?=number_format($season->priceTowels, 2, ',', '.')?> €</td>
                                <td class="d-flex justify-content-center gap-2">
                                    <div class="d-flex justify-content-center gap-2">
                                        <form action="season_report.php" method="POST">
                                            <input type="hidden" name="action" value="view_report">
                                            <input type="hidden" name="year" value="<?=$season->year?>">
                                            <button type="submit" class="btn btn-warning">Report</button>
                                        </form>

                                        <?php if($season->year == date("Y")){ ?>
                                            <button class="btn btn-warning change-season">Modifica</button>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <!-- Adding JS scripts -->
    <script src="js/dashboard_admin.js"></script>
    </body>
</html>
