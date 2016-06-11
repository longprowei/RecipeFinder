<?php

/**
 * Process items data from csv file
 *
 * @param string $itemsFileName name of items file
 *          file format:
 *              bread,10,slices,25/7/2016
 *              cheese,10,slices,25/7/2016
 *              ...
 *
 * @return array|boolean return items array or false 
 */
function processItemsCsv($itemsFileName) {
    $itemsHandle = fopen($itemsFileName, "r");
    if (!$itemsHandle) {
        print("Cannot access file $itemsFileName\n");
        return false;
    }

    $items = array();
    while (($item = fgetcsv($itemsHandle, 0, ',')) !== FALSE) {
        if (count($item) !== 4) {
            print("$itemsFileName: wrong file format\n");
            return false;
        }

        $item = array(
            'item' => $item[0], 
            'amount' => $item[1], 
            'unit' => $item[2], 
            'useby' => $item[3]
        );

        //convert time format from '1/1/1970' to '1-1-1970' for function strtotime
        $item['useby'] = str_replace('/', '-', $item['useby']);
        array_push($items, $item);
    }

    if (count($items) === 0) {
        print("$itemsFileName is empty\n");
        return false;
    }

    return $items;
}

/**
 * Process items data, just accept csv file now
 *
 * @param string $itemsFileName name of items file
 * @param string $itemsFileType type of items file, default is csv
 * @return array|boolean return items array or false 
 *          array format: 
 *          [[
 *              'item' => 'bread', 
 *              'amount' => '1', 
 *              'unit' => 'slice', 
 *              'useby' => '1-1-1970'
 *          ], [
 *              ....
 *          ]]
 */
function processItems($itemsFileName, $itemsFileType = 'csv') {
    if (!$itemsFileName) {
        echo "items file name is empty\n";
        return false;
    }

    if (!$itemsFileType) {
        echo "items file type is empty\n";
        return false;
    }

    switch ($itemsFileType) {
        case 'csv':
            return processItemsCsv($itemsFileName); 
        default:
            echo "$itemsFileType is not acceptable for items file\n";
            return false;
    }
}

/**
 * Process recipes data form json file
 *
 * @param string $recipesFileName
 *      format:
 *        [{
 *            "name": "grilled cheese on toast",
 *            "ingredients": [{ 
 *                "item":"bread", 
 *                "amount":"2", 
 *                "unit":"slices"
 *            }, {
 *                "item":"cheese", 
 *                "amount":"2",
 *                "unit":"slices"
 *            }]
 *        }, {
 *           ...
 *        }]
 * @return array|boolean recipes array
 */
function processRecipesJson($recipesFileName) {
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

/**
 * Process recipes file, just accept json file now
 *
 * @param string $recipesFileName recipes file name
 * @param string $recipesFileType recipes file type, default is json
 * @return array|boolean
 *     format:
 *       [[
 *           "name" => "grilled cheese on toast",
 *           "ingredients" => [[
 *               "item" => "bread", 
 *               "amount" => "2", 
 *               "unit" => "slices",
 *           ], [
 *               "item" => "cheese", 
 *               "amount" => "2", 
 *               "unit" => "slices",
 *           ]]
 *       ], [
 *       ...
 *       ]
 */
function processRecipes($recipesFileName, $recipesFileType = 'json'){
    if (!$recipesFileName) {
        echo "recipes file name is empty\n";
        return false;
    }

    if (!$recipesFileType) {
        echo "recipes file type is empty\n";
        return false;
    }

    switch ($recipesFileType) {
        case 'json':
            return processRecipesJson($recipesFileName); 
        default:
            echo "$recipesFileType is not acceptable for recipes file\n";
            return false;
    }
}

/**
 * match a recipe accroding to items
 *
 * @param array $items processed items array
 * @param array &$recipe processed recipe array, output param
 * @return boolean wheather this recipe found its all items
 *
 */
function matchRecipe($items, &$recipe) {
    $matchItemCount = 0;
    $today = strtotime('today');
    foreach ($recipe["ingredients"] as &$i) {
        foreach($items as $j) {
            $timeStamp = strtotime($j['useby']);
            if ($i['item'] === $j['item'] && 
                intval($i['amount']) <= intval($j['amount']) && 
                $i['unit'] === $j['unit'] && 
                $today <= $timeStamp) {
                    //found a item, add date info
                    $i['timestamp'] = $timeStamp;
                    $i['useby'] = $j['useby'];
                    $matchItemCount++;
            }
        }
    }

    //return true if found all items for this recipe
    if ($matchItemCount === count($recipe["ingredients"])) {
        return true;
    } else {
        return false;
    }
}

/**
 * Find all matched recipes accroding to items and recipes
 *
 * @param array $items processed items array
 * @param array $recipes processed recipes array
 * @return array all matched recipes array
 *
 */
function findAllRecipes($items, $recipes) {
    $existRecipes = array();
    foreach ($recipes as $value) {
        if (matchRecipe($items, $value)) {
            array_push($existRecipes, $value);
        }
    }

    return $existRecipes;
}

/**
 * Find final recipe accroding to useby of items
 *
 * @param array $existRecipes all matched recipes
 * @return string the name of final recipe
 */
function findRecipe($existRecipes) {
    if (empty($existRecipes)) {
        return "Order Takeout";
    }

    $closestTime = $existRecipes[0]["ingredients"][0]["timestamp"];
    $suitableRecipe = $existRecipes[0];
    //find a closest use-by item and set its recipe as final recipe
    foreach ($existRecipes as $recipe) {
        foreach($recipe["ingredients"] as $item) {
            if ($item["timestamp"] < $closestTime) {
                $closestTime = $item["timestamp"];
                $suitableRecipe = $recipe;
            }
        }
    }    

    return $suitableRecipe["name"];
}

/**
 * Input itmes and recipes name and their file type to get a recipe
 *
 * @param string $itemsFileName items file name
 * @param string $recipesFileName recipes file name
 * @param string $itemsFileType type of items file, default is csv
 * @param string $recipesFileType type of recipes file, default json
 * @return string the recipe name
 *
 */
function getRecipe($itemsFileName, $recipesFileName, $itemsFileType = 'csv', $recipesFileType = 'json') {
    $items = processItems($itemsFileName, $itemsFileType);
    if (!$items) return;

    $recipes = processRecipes($recipesFileName, $recipesFileType);
    if (!$recipes) return;

    $existRecipes = findAllRecipes($items, $recipes);
    return findRecipe($existRecipes);
}

?>
