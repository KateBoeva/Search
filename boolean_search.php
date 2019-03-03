<?php

$needed = readline();
$words = explode(" ", $needed);

$list = scandir('data/matrix/words');

foreach ($list as $key => $word) {
    $p = strpos($word, '.txt');
    if ($p === false) {
        unset($list[$key]);
    } else {
        $list[$key] = substr($list[$key], 0, $p);
    }
}

$result = [];
foreach ($words as $word) {
    if (!in_array($word, $list)) {
        exit($word . " not found");
    } else {
        $result[$word] = explode("\t", file_get_contents('data/matrix/words/'.$word.'.txt'));
    }
}

if (count($result) == 0) {
    exit();
} elseif (count($result) == 1) {
    print_r(implode(" ", $result[$words[0]]));
    exit();
}

$intersect = [];
for ($i = 0; $i < count($result)-1; $i++) {
    if (empty($intersect)) {
        if ($i == 0) {
            $intersect = array_intersect($result[$words[$i]], $result[$words[$i+1]]);
        } else {
            exit("Not found.");
        }
    } else {
        $intersect = array_intersect($intersect, $result[$words[$i+1]]);
    }
}

print_r(implode("\t", $intersect));
