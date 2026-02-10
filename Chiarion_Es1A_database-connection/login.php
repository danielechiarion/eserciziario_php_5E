<?php session_start(); // start session ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
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
        <div class="col-2"></div>
        <div class="col-8">
            <form action="dashboard.php" method="POST" class="d-flex flex-column">
                <input type="text" placeholder="Inserisci username..." name="username" required>
                <input type="password" name="password" required>
                <button type="submit">Access System</button>
            </form>
        </div>
        <div class="col-2"></div>
    </div>
</div>
<?php if (isset($_SESSION['error_message']) && $_SESSION['error_message']): ?>
    <div class="alert alert-danger mt-3 d-flex justify-content-center px-3">
        <strong>Access Denied:</strong> <?php echo htmlspecialchars($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>
</body>
</html>