#Recipe Finder

### run unit test
`./phpunit-5.4.2.phar --bootstrap src/recipeFinderFuns.php tests` in project directory

or 

`phpunit --bootstrap src/recipeFinderFuns.php tests` in project directory if already installed phpunit

### create phar file
`php createPhar.php`

### run program
`./recipeFinder.phar -i items-file -r recipes-file`

eg:

`./recipeFinder.phar -i tests/fridge1.csv -r tests/recipes.json`
