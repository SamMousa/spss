<?php

require_once __DIR__ .'/../vendor/autoload.php';

$reader = \SPSS\Sav\Reader::fromString(file_get_contents( __DIR__ . '/data/pspp.sav'));

print_r($reader);
