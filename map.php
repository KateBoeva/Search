<?php

require_once 'phpmorphy-0.3.7/src/common.php';
include 'SiteMap.php';
include 'Matrix.php';

$dir = 'phpmorphy-0.3.7/dicts';
$lang = 'en_EN';
$opts = ['storage' => PHPMORPHY_STORAGE_FILE];

try {
    $morphy = new phpMorphy($dir, $lang, $opts);
} catch (phpMorphy_Exception $e) {
    die('Error occured while creating phpMorphy instance: ' . $e->getMessage());
}

$map = new SiteMap();

$map->refreshHostPaths();
$map->updateFiles();
$map->lemmatizeFiles($morphy);

$matrix = new Matrix($map);
$matrix->buildMatrix();
