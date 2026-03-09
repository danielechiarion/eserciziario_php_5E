<?php
function drawTable($rows, $columns){
    echo "<table border='1'>";

    for($i=0; $i<$rows; $i++){
        echo "<tr>";
        for($j=0;$j<$columns;$j++){
            if(($j%2==0 && $i%2==0) || ($j%2!=0 && $i%2!=0))
                echo '<td class="giallo"></td>';
            else
                echo '<td class="verde"></td>';
        }
        echo "</tr>";
    }

    echo "</table>";
}