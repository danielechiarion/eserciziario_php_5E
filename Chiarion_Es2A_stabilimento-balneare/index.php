<?php
include 'classes/Account.php';
include 'classes/Person.php';

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
 * Function to make all the necessary configurations
 * at the beginning of the configuration, such as
 * necessary data on the database
 * @return void
 */
function load_config($database_data){
    /* create the connection with the database */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if($connection->connect_error)
        die("Connection failed: ".$connection->connect_error);

    /* the necessary configuration is to add
    an admin account that will be added if not present
    or ignored. This won't reset the possible change of the password */
    $USERNAME = "admin";
    $PASSWORD = password_hash("admin", PASSWORD_DEFAULT);
    $ROLE = AccountType::Administrator->value;
    $query = $connection->prepare("INSERT IGNORE INTO account (username, password, role) 
                                            VALUES (?, ?, ?)");
    $query->bind_param("sss", $USERNAME, $PASSWORD, $ROLE);
    $query->execute();

    $connection->close();
}

/**
 * Function to get the test the login and select the corresponding
 * page based on the type of account
 */
function login_account($database_data)
{
    /* create the connection with the database */
    $connection = new mysqli($database_data['host'], $database_data['username'], $database_data['password'], $database_data['database']);
    if ($connection->connect_error)
        die("Connection failed: " . $connection->connect_error);

    /* get the data from the post */
    $username = $_POST['username'];
    $password = $_POST['password'];

    /* make the request of the username */
    $query = $connection->prepare("SELECT username, password, cliente, role FROM Account WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();

    /* compare the username and passwords
    with the results */
    $result = $query->get_result();
    if ($result->num_rows == 0) {
        echo json_encode(['success' => false]);
        $connection->close();
        return;
    }
    $row = $result->fetch_assoc();
    if (!password_verify($password, $row['password'])) {
        echo json_encode(['success' => false]);
        $connection->close();
        return;
    }

    /* otherwise get the account,
    control if it's a client and get the corresponding data */
    if ($row['cliente'] != null) {
        $query = $connection->prepare("SELECT nome, cognome FROM persona WHERE CF = ?");
        $query->bind_param("s", $row['cliente']);
        $query->execute();
        $result = $query->get_result()->fetch_assoc();
        $connection->close();

        $_SESSION['user'] = new Account($row['username'], AccountType::from($row['role']),
                new Person($row['cliente'], $result['nome'], $result['cognome']));

        echo json_encode(['success' => true, 'redirect' => 'dashboard_client.php']);
    } else {
        $connection->close();
        $_SESSION['user'] = new Account($row['username'], AccountType::from($row['role']));
        echo json_encode(['success' => true, 'redirect' => 'dashboard_admin.php']);
    }
}

/* start the session and
get the data from the database */
session_start();
$database_data = get_database_parameters();

/* get the starting configuration first */
if(!isset($_SESSION['first_access']) || $_SESSION['first_access']){
    load_config($database_data);
    $_SESSION['first_access'] = false;
}

/* handle the POST request for the login */
if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['action'] == "login") {
    login_account($database_data);
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-2"></div>
        <div class="col-8 d-flex justify-content-center">
            <h1>Login</h1>
        </div>
        <div class="col-2"></div>
    </div>
    <div class="row">
        <div class="col-12 justify-content-center">
            <form class="d-flex flex-column" id="login-form">
                <input type="text" placeholder="Inserisci username..." name="username" class="form-control item-space" required>
                <input type="password" name="password" class="form-control item-space" required>
                <button id="login-button" type="submit" name="action" value="login" class="btn btn-primary item-space">Login</button>
                <button class="btn btn-secondary item-space" onclick="window.location.href='sign-up.php'">Registrati</button>
            </form>
        </div>
    </div>
</div>
<script>
    $("#login-form").on("submit", function(e){
        e.preventDefault(); // blocca il submit normale

        /* get form data and convert to object */
        var formData = {
            username: $('input[name="username"]').val(),
            password: $('input[name="password"]').val(),
            action: 'login'
        };

        $.ajax({
            url: "<?=$_SERVER['PHP_SELF']?>",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response){
                console.log('Login response:', response);
                if(response.success){
                    window.location.href = response.redirect;
                } else {
                    $("#login-error").removeClass("d-none");
                }
            },
            error: function(xhr, status, error){
                console.error("Login error:", error, xhr.responseText);
                $("#login-error").removeClass("d-none");
            }
        });
    });
</script>

<div class="alert alert-danger mt-3 d-flex justify-content-center px-3 d-none" id="login-error">
    <strong>Accesso negato</strong>
</div>
</body>
</html>
