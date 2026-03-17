<?php session_start(); // start session ?>
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
            url: "dashboard.php",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function(response){
                console.log('Login response:', response);
                if(response.success){
                    window.location.href = "dashboard.php";
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