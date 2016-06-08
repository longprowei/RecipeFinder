<?php
require_once("recipeFinderFuns.php");
global $argc, $argv;
if ($argc != 3) {
    usage();
    exit(1);
}

date_default_timezone_set('Australia/Sydney');
$items = processItems($argv[1]) or exit(1);
$recipes = processRecipes($argv[2]) or exit(1);
$existRecipes = findRecipes($items, $recipes);
printResult($existRecipes);

?>
