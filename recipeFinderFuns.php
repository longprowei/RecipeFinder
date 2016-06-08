<?php

function usage() {
    echo "usage: recipeFinder items recipes\n";
}

function processItems($itemsFileName) {
    $itemsHandle = fopen($itemsFileName, "r");
    if (!$itemsHandle) {
        print("Cannot access file $itemsFileName\n");
        return false;
    }

    $items = array();
    while (($item = fgetcsv($itemsHandle, 0, ",")) !== FALSE) {
        if (count($item) !== 4) {
            print("$itemsFileName: wrong file format\n");
            return false;
        }

        //convert time format from '1/1/1970' to '1-1-1970' for function strtotime
        $item[3] = str_replace('/', '-', $item[3]);
        array_push($items, $item);
    }

    if (count($items) === 0) {
        print("$itemsFileName is empty\n");
        return false;
    }

    return $items;
}

function processRecipes($recipesFileName) {
    $recipesStr = file_get_contents($recipesFileName);
    if ($recipesStr === FALSE) {
        print("Cannot access file $recipesFileName\n");
        return false;
    } else if (!$recipesStr) {
        print("$recipesFileName is empty\n");
        return false;
    }

    $recipes = json_decode($recipesStr, true);
    if (!$recipes) {
        print("$recipesFileName: wrong file format\n");
        return false;
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
    $existRecipes = array();
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
        return;
    }

    $suitableRecipe = array();
    foreach ($existRecipes as $recipe) {
        $closestTime = $recipe["ingredients"][0]["timestamp"];
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
?>
