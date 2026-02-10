<?php
$connection = new mysqli("", "daniele_chiarion", "", "daniele_chiarion");

if($connection->connect_error)
    die("Connection failed: ".$connection->connect_error);

$connection->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Connessione</title>
    <style>
        .success-box {
            max-width: 420px;
            margin: 80px auto;
            padding: 20px 24px;
            border-radius: 6px;
            border: 1px solid #c3e6cb;
            background-color: #d4edda;
            color: #155724;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }

        .success-box h2 {
            margin: 0 0 8px;
            font-size: 1.2rem;
        }

        .success-box p {
            margin: 0;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>

<div class="success-box">
    <h2>Connessione riuscita</h2>
    <p>Il database è stato collegato correttamente.</p>
</div>

</body>
</html>
