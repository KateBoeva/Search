<?php

include 'SiteMap.php';

$pages = explode("\n", file_get_contents('data/index.txt'));
$map = new SiteMap();

$table = [];
foreach ($pages as $page) {
    $links = $map->findPages($page);
    $doc = "";
    foreach ($pages as $link) {
        $doc .= in_array($link, $links) ? '1' : '0';
    }
    $table[] = $doc;
}

file_put_contents('data/pagerank.txt', implode("\n", $table));
