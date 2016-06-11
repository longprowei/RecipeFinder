<?php

require_once("recipeFinderFuns.php");

function usage() {
    echo "usage: recipeFinder -i items-file -r recipes-file\n" .
        "[--items-file-type=csv] [--recipes-file-type=json]\n";
}

error_reporting(0);
$longopts = array(
    'items-file-type:', //default is csv
    'recipes-file-type:' //default is json
);

$options = getopt('i:r:h', $longopts);

$itemsFileName = '';
$recipesFileName = '';
$itemsFileType = 'csv';
$recipesFileType = 'json';

foreach (array_keys($options) as $opt) {
    switch ($opt) {
        case 'i':
            $itemsFileName = $options['i']; 
            break;
        case 'r':
            $recipesFileName = $options['r'];
            break;
        case 'items-file-type':
            $itemsFileType = $options['items-file-type'];
            break;
        case 'recipes-file-type':
            $recipesFileType = $options['recipes-file-type'];
            break;
        case 'h':
            usage();
            exit(0);
        default:
            echo "Invalid option: $opt\n";
            usage();
            exit(1);
    }
}

if (!$itemsFileName)  {
    echo "Please indecate items file\n";
    usage();
    exit(1);
}
if (!$recipesFileName) {
    echo "Please indecate recipes file\n";
    usage();
    exit(1);
}


$recipe = getRecipe($itemsFileName, $recipesFileName, $itemsFileType, $recipesFileType);
if (!empty($recipe)) {
    echo "$recipe\n";
}

?>
