<?php

class Matrix
{
    public $map;

    public function __construct(SiteMap $map)
    {
        $this->map = $map;
        file_put_contents('data/matrix/wordslist.txt', implode("\n", $this->findUniqueWords()));
    }

    private function findUniqueWords()
    {
        $words = [];

        for ($i = 0; $i < $this->map->limit; $i++) {
            $words = array_merge($words, $this->map->getLemmatizedFile('data/lemmatized/'.($i+1).'.txt'));
        }

        $result = [];
        $stop_words = explode("\n", file_get_contents('data/stop_words.txt'));
        foreach ($words as $word) {
            $word = strtolower($word);
            if (!in_array($word, array_merge($result, $stop_words)) && preg_match('/^[à-ÿÀ-ß0-9-]+$/', $word)) {
                $result[] = $word;
            }
        }

        return $result;
    }

    public function getUniqueList()
    {
        return explode("\n", file_get_contents('data/matrix/wordslist.txt'));
    }

    public function buildMatrix()
    {
        $unique = $this->getUniqueList();

        for ($i = 0; $i < count($unique); $i++) {
            $result = [];
            for ($j = 1; $j < $this->map->limit; $j++) {
                $file = $this->map->getLemmatizedFile('data/lemmatized/'.$j.'.txt');
                if (array_search($unique[$i], $file)) {
                    $result[] = $j;
                }
            }
            file_put_contents('data/matrix/words/'.($i+1).'.txt', implode("\t", $result));
        }
    }
}
