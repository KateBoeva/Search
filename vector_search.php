<?php

include 'TfIdf_Helper.php';
require_once 'phpmorphy-0.3.7/src/common.php';

$dir = 'phpmorphy-0.3.7/dicts';
$lang = 'en_EN';
$opts = ['storage' => PHPMORPHY_STORAGE_FILE];

try {
    $morphy = new phpMorphy($dir, $lang, $opts);
} catch (phpMorphy_Exception $e) {
    die('Error occured while creating phpMorphy instance: ' . $e->getMessage());
}

//$words = explode(" ", strtolower($morphy->lemmatize(strtoupper(readline()))[0]));
$helper = new TfIdf_Helper();

//$helper->calcTfIdf();
$cosSim = $helper->cosSim(['leader', 'school']);
$helper->printFirst(20, $cosSim);
