<?php

class Matrix
{
    public $map;

    public function __construct(SiteMap $map)
    {
        $this->map = $map;
    }

    public static function getWordsFromMatrix()
    {
        $list = scandir('data/matrix/words');

        foreach ($list as $key => $word) {
            $p = strpos($word, '.txt');
            if ($p === false) {
                unset($list[$key]);
            } else {
                $list[$key] = substr($list[$key], 0, $p);
            }
        }

        return $list;
    }

    private function getUniqueWords()
    {
        $words = [];

        for ($i = 0; $i < $this->map->limit; $i++) {
            $words = array_merge($words, $this->map->getLemmatizedFile('data/lemmatized/'.($i+1).'.txt'));
        }

        $result = [];
        $stop_words = explode("\n", file_get_contents('data/stop_words.txt'));
        foreach ($words as $word) {
            $word = strtolower($word);
            if (!in_array($word, array_merge($result, $stop_words)) && preg_match('/^[A-Za-z0-9-]+$/', $word)) {
                $result[] = $word;
            }
        }

        return $result;
    }

    public function buildMatrix()
    {
        $unique = $this->getUniqueWords();

        foreach ($unique as $word) {
            $result = [];
            for ($i = 1; $i <= $this->map->limit; $i++) {
                $file = $this->map->getLemmatizedFile('data/lemmatized/'.$i.'.txt');
                if (in_array($word, $file)) {
                    $result[] = $i;
                }
            }
            file_put_contents('data/matrix/words/'.$word.'.txt', implode("\t", $result));
        }
    }

    public function findTfIdfQuery()
    {
        $list = Matrix::getWordsFromMatrix();
        $result = [];

        foreach ($list as $word) {
            for ($i = 1; $i <= $this->map->limit; $i++) {
                $file = explode("\t", file_get_contents('data/lemmatized/'.$i.'.txt'));
                if ($count = (float)array_count_values($file)[$word] > 0) {
                    $result[$word][$i] = $count / count($file);

                }
            }
        }

        foreach ($result as $word => $docs) {
            foreach ($docs as $doc => $tf) {
                $result[$doc][$word] = $tf * (1.0 / count($docs));
            }
        }

        return $result;
    }

    public function findQueryVector($search)
    {
        $list = Matrix::getWordsFromMatrix();
        $result = [];
        $sum = 0;

        foreach ($list as $word) {
            $result[$word] = 1.0 / count(explode("\t", 'data/matrix/words/'.$word.'.txt'));
        }

        foreach ($search as $word) {
            $in_query = array_count_values($search)[$word];
            $in_col = count($list);
            $sum += pow(2,($in_query/$in_col)*$result[$word]);
        }

        return sqrt((float)$sum);
    }

    public function findDocsVectors($search)
    {
        $result = $this->findTfIdfQuery();
        $vectors = [];

        for ($i = 1; $i < $this->map->limit; $i++) {
            $sum = 0;
            foreach ($search as $word) {
                if ($result[$i][$word]) {
                    $sum += pow(2, $result[$i][$word]);
                }
            }
            $vectors[$i] = sqrt((float)$sum);
        }

        arsort($vectors);

        return $vectors;
    }

    public function printFirst($count, $list)
    {
        print_r(array_keys(array_slice($list, 0, $count, true)));
    }

    public function tfidf($search)
    {
        $search = explode(" ", $search);
        $query = $this->findQueryVector($search);
        $docs = $this->findDocsVectors($search);

        $div = [];
        foreach ($docs as $doc => $num) {
            $div[$doc] = abs($query - $num);
        }
        asort($div);

        $this->printFirst(20, $div);
    }
}
