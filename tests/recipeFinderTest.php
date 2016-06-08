<?php
use PHPUnit\Framework\TestCase;

class recipeFinder extends TestCase
{
    public function testProcessItemsNoaccess() {
        $itemsFileName = 'noexist.txt';
        $this->assertFalse(@processItems($itemsFileName));
        $this->expectOutputString("Cannot access file $itemsFileName\n");
    }

    public function testProcessItemsWrongFormat() {
        $itemsFileName = 'wrong_format.txt';
        $this->assertFalse(processItems($itemsFileName));
        $this->expectOutputString("$itemsFileName: wrong file format\n");
    }

    public function testProcessItemsEmpty() {
        $itemsFileName = 'empty.txt';
        $this->assertFalse(processItems($itemsFileName));
        $this->expectOutputString("$itemsFileName is empty\n");
    }
    
    public function testProcessItemsResult() {
        date_default_timezone_set('Australia/Sydney');
        $itemsFileName = 'fridge.csv';
        $expected = [
            ["bread","10","slices","25-7-2016"],
            ["cheese","10","slices","25-7-2016"],
            ["butter","250","grams","25-7-2016"],
            ["peanut butter","250","grams","4-6-2016"],
            ["mixed salad","500","grams","1-1-2016"]
        ];
        $this->assertEquals($expected, processItems($itemsFileName));
        return $expected;
    }

    public function testProcessRecipesNoaccess() {
        $recipesFileName = 'noexist.txt';
        $this->assertFalse(@processRecipes($recipesFileName));
        $this->expectOutputString("Cannot access file $recipesFileName\n");

    }

    public function testProcessRecipesWrongFormat() {
        $recipesFileName = 'wrong_format.txt';
        $this->assertFalse(processRecipes($recipesFileName));
        $this->expectOutputString("$recipesFileName: wrong file format\n");
    }

    public function testProcessRecipesEmpty() {
        $recipesFileName = 'empty.txt';
        $this->assertFalse(processRecipes($recipesFileName));
        $this->expectOutputString("$recipesFileName is empty\n");
    }

    public function testProcessRecipesResult() {
        $recipesFileName = 'recipes.json';
        $recipesStr = file_get_contents($recipesFileName);
        $recipes = json_decode($recipesStr, true);
        $this->assertEquals($recipes, processRecipes($recipesFileName));
        return $recipes;
    }

    /**
     * @depends testProcessItemsResult
     * @depends testProcessRecipesResult
     */
    public function testMatchRecipe($items, $recipes) {
        $this->assertTrue(matchRecipe($items, $recipes[0]));
        $this->assertFalse(matchRecipe($items, $recipes[1]));
    }

    /**
     * @depends testProcessItemsResult
     * @depends testProcessRecipesResult
     */
    public function testFindRecipes($items, $recipes) {
        $expected = [[
            "name" => "grilled cheese on toast",
            "ingredients" => [[
                "item" => "bread", 
                "amount" => "2", 
                "unit" => "slices",
                "timestamp" => 1469368800, 
                "useby" => "25-7-2016"
            ], [
                "item" => "cheese", 
                "amount" => "2", 
                "unit" => "slices",
                "timestamp" => 1469368800, 
                "useby" => "25-7-2016"
            ]]
        ]];
        $this->assertEquals($expected, findRecipes($items, $recipes));
        return $expected;
    }

    /**
     * @depends testFindRecipes
     */
    public function testPrintResult($existRecipes) {
        printResult($existRecipes);
        $this->expectOutputString("grilled cheese on toast\n");
    }

    private function excuteAll($itemsFileName, $recipesFileName) {
        date_default_timezone_set('Australia/Sydney');
        $items = processItems($itemsFileName);
        $recipes = processRecipes($recipesFileName);
        $existRecipes = findRecipes($items, $recipes);
        printResult($existRecipes);
    }
    public function testFinalResult1() {
        $this->excuteAll("fridge1.csv", "recipes.json");
        $this->expectOutputString("salad sandwich\n");
    }

    public function testFinalResult2() {
        $this->excuteAll("fridge2.csv", "recipes.json");
        $this->expectOutputString("Order Takeout\n");
    }
}
?>
