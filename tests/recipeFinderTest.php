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
        $itemsFileName = 'tests/wrong_format.txt';
        $this->assertFalse(processItems($itemsFileName));
        $this->expectOutputString("$itemsFileName: wrong file format\n");
    }

    public function testProcessItemsEmpty() {
        $itemsFileName = 'tests/empty.txt';
        $this->assertFalse(processItems($itemsFileName));
        $this->expectOutputString("$itemsFileName is empty\n");
    }
    
    public function testProcessItemsResult() {
        date_default_timezone_set('Australia/Sydney');
        $itemsFileName = 'tests/fridge.csv';
        $expected = [
            ["item" => "bread", "amount" => "10", "unit" => "slices", "useby" => "25-7-2016"],
            ["item" => "cheese", "amount" => "10", "unit" => "slices", "useby" => "25-7-2016"],
            ["item" => "butter", "amount" => "250", "unit" => "grams", "useby" => "25-7-2016"],
            ["item" => "peanut butter", "amount" => "250", "unit" => "grams", "useby" => "4-6-2016"],
            ["item" => "mixed salad", "amount" => "500", "unit" => "grams", "useby" => "1-1-2016"]
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
        $recipesFileName = 'tests/wrong_format.txt';
        $this->assertFalse(processRecipes($recipesFileName));
        $this->expectOutputString("$recipesFileName: wrong file format\n");
    }

    public function testProcessRecipesEmpty() {
        $recipesFileName = 'tests/empty.txt';
        $this->assertFalse(processRecipes($recipesFileName));
        $this->expectOutputString("$recipesFileName is empty\n");
    }

    public function testProcessRecipesResult() {
        $recipesFileName = 'tests/recipes.json';
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
    public function testFindAllRecipes($items, $recipes) {
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
        $this->assertEquals($expected, findAllRecipes($items, $recipes));
        return $expected;
    }

    /**
     * @depends testFindAllRecipes
     */
    public function testFindRecipe($existRecipes) {
        $this->assertEquals("grilled cheese on toast", findRecipe($existRecipes));
    }

    public function testGetRecipe1() {
        $this->assertEquals("salad sandwich", getRecipe("tests/fridge1.csv", "tests/recipes.json"));
    }

    public function testGetRecipe2() {
        $this->assertEquals("Order Takeout", getRecipe("tests/fridge2.csv", "tests/recipes.json"));
    }

    public function testGetRecipe3() {
        $this->assertEquals("grilled cheese on toast", getRecipe("tests/fridge3.csv", "tests/recipes.json"));
    }
}
?>
