<?php

function usage() {
    echo "usage: recipeFinder items recipes\n";
}

function processItems($itemsFileName) {
    $itemsHandle = fopen($itemsFileName, "r");
    if (!$itemsHandle) {
        die("Cannot access file $itemsFileName\n");
    }

    $items = Array();
    while (($item = fgetcsv($itemsHandle, 0, ",")) !== FALSE) {
        if ($item === NULL) {
            die("$itemsFileName: wrong file format\n");
        }

        //convert time format from '1/1/1970' to '1-1-1970' for function strtotime
        $item[3] = str_replace('/', '-', $item[3]);
        array_push($items, $item);
    }

    if (count($items) === 0) {
        die("$itemsFileName is empty\n");
    }

    return $items;
}

function processRecipes($recipesFileName) {
    $recipesStr = file_get_contents($recipesFileName);
    if ($recipesStr === FALSE) {
        die("Cannot access file $recipesFileName\n");
    } else if (!$recipesStr) {
        die("$recipesFileName is empty\n");
    }

    $recipes = json_decode($recipesStr, true);
    if (!$recipes) {
        die("$recipesFileName: wrong file format\n");
    }

    return $recipes; 
}

function matchRecipe($items, &$recipe) {
    $matchItemCount = 0;
    $today = strtotime('today');
    foreach ($recipe["ingredients"] as &$i) {
        foreach($items as $j) {
            $timeStamp = strtotime($j[3]);
            if ($i["item"] === $j[0] && 
                $i["amount"] <= $j[1] && 
                $i["unit"] === $j[2] && 
                $today <= $timeStamp) {
                    //use timestamp for useby
                    $i["timestamp"] = $timeStamp;
                    $i["useby"] = $j[3];
                    $matchItemCount++;
            }
        }
    }

    if ($matchItemCount === count($recipe["ingredients"])) {
        return true;
    } else {
        return false;
    }
}

function findRecipes($items, $recipes) {
    $existRecipes = Array();
    foreach ($recipes as $value) {
        if (matchRecipe($items, $value)) {
            array_push($existRecipes, $value);
        }
    }

    return $existRecipes;
}

function printResult($existRecipes) {
    if (empty($existRecipes)) {
        echo "Order Takeout\n";
        exit(0);
    }

    $suitableRecipe = Array();
    foreach ($existRecipes as $recipe) {
        $closestTime = $recipe["ingredients"]["useby"];
        $suitableRecipe = $recipe;
        foreach($recipe["ingredients"] as $item) {
            if ($item["timestamp"] < $closestTime) {
                $closestTime = $item["timestamp"];
                $suitableRecipe = $recipe;
            }
        }
    }    

    echo $suitableRecipe["name"] . "\n";
}

//main
if ($argc != 3) {
    usage();
    exit(1);
}

date_default_timezone_set('Australia/Sydney');
$items = processItems($argv[1]);
$recipes = processRecipes($argv[2]);
$existRecipes = findRecipes($items, $recipes);
printResult($existRecipes);

?>
