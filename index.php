<?php

include 'SiteMap.php';

$url = 'https://kpfu.ru';
$ignoreList = ["javascript:", ".css", ".js", ".ico", ".jpg", ".png", ".jpeg", ".swf", ".gif", '#', '@'];
$map = new SiteMap($url, $ignoreList);
$map->updateListFile();
$map->updateFilesContent();
