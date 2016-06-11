<?php

$phar = new Phar("recipeFinder.phar");
$phar->startBuffering();
$defaultStub = $phar->createDefaultStub('recipeFinder.php');
$phar->buildFromDirectory(__DIR__ . "/src", '/\.php$/');
$stub = "#!/usr/bin/env php\n" . $defaultStub;
$phar->setStub($stub);
$phar->stopBuffering();
chmod('recipeFinder.phar',0555);

?>
