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
$map = new SiteMap($url);
//print_r($map->findPages($url));
//echo $map->makeFullLink('#fsf');
$map->refreshHostPaths();
$map->updateFiles();
$map->lemmatizeFiles($morphy);
$matrix = new Matrix($map);
$matrix->buildMatrix();


//for ($i = 1; $i < 5689; $i++) {
//    unlink('data/matrix/words/'.$i.'.txt');
//}

//file_put_contents('test.txt', implode("\n", $map->lemmatizeFiles($morphy, 'data/pages/1.txt')));
//$map->lemmatizeFiles($morphy, )

//var_dump(mb_convert_encoding($morphy->lemmatize(mb_convert_encoding('КОШКИ', 'windows-1251'))[0]));
