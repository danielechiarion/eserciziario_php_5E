<?php
/* definition of the list of values */
$interests = array("techno"=>"Tecnologia", "moto"=>"Motori", "travel"=>"Viaggi");
$information_media = array("press"=>"Giornali", "tv"=>"TV", "internet"=>"Internet", "friends"=>"Amici");

if($_SERVER['REQUEST_METHOD'] == "POST"){
    foreach($_POST as $key=>$value)
        echo "Il campo POST [$key] contiene il valore [$value]<br>";
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Form Foreach</title>
    </head>
    <body>
        <form action="index.php" method="POST">
            Nome e cognome: <input type="text" name="username"><br><br>
            Stato civile: <br>
            <input type="radio" name="civil" value="coniugato">Coniugato<br>
            <input type="radio" name="civil" value="non coniugato">Non coniugato<br><br>
            Argomenti d'interesse: <br>
            <?php
                foreach($interests as $key => $value)
                    echo "<input type='checkbox' name='$key'>$value<br>"
            ?>
            <br>
            Dove hai saputo del nostro sito?
            <select name="site">
                <?php
                    foreach($information_media as $key => $value)
                        echo "<option value='$key'>$value</option>"
                ?>
            </select> <br>
            <input type="submit">
        </form>
        <hr>
    </body>
</html>