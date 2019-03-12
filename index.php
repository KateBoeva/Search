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

$url = 'https://www.yale.edu';
$count = 100;
$map = new SiteMap($url, $count);

$map->refreshHostPaths();
$map->updateFiles();
$map->lemmatizeFiles($morphy);

$matrix = new Matrix($map);
$matrix->buildMatrix();

$search = strtolower($morphy->lemmatize(strtoupper(readline()))[0]);
$matrix->tfidf($search);
