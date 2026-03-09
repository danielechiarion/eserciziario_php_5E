<?php
function nation_for_town($list, $town){
    if(isset($list[$town]))
        echo "La città {$town} è in {$list[$town]}\n";
}

function town_for_nation($list, $nation){
    $index = array_search($nation, array_values($list));

    if($index){
        $town = array_keys($list)[$index];
        echo "La nazione {$nation} ospita la città {$town}\n";
    }
}

function print_all($list){
    if(count($list) == 0)
        return;

    foreach($list as $town => $nation){
        echo "{$nation} - {$town}\t";
    }
}

$nation_list = array(
    "Rovigo" => "Italia",
    "Dusseldorf" => "Germania",
    "Sakhir" => "Bahrein",
    "Siviglia" => "Spagna",
    "Rotterdam" => "Olanda",
    "Melbourne" => "Australia"
);

nation_for_town($nation_list, "Rovigo");
town_for_nation($nation_list, "Olanda");
print_all($nation_list);