<?php

class SiteMap
{
    public $host;
    public $host_paths;
    public $ignore_list;
    public $limit;

    public function __construct($host)
    {
        $this->host = $host;
        $this->limit = 100;
        $this->host_paths = [];
        $this->ignore_list = ["javascript:", ".css", ".js", ".ico", ".jpg", ".png", ".jpeg", ".swf", ".gif", '#', '@'];
    }

    public function findPaths()
    {
        if (file_exists('data/index.txt') && $content = file_get_contents('data/index.txt')) {
            $this->host_paths = explode("\n", $content);
        } else {
            $this->host_paths = $this->filter($this->findPages($this->host));
        }
    }

    private function getRespCode($url)
    {
        return substr(get_headers($url)[0], 9, 3);
    }

    private function findPages($page)
    {
        $content = file_get_contents($page);
        preg_match_all("/<a[^>]*href\s*=\s*'([^']*)'|" . '<a[^>]*href\s*=\s*"([^"]*)"' . "/is", $content, $match);

        return $match[2];
    }

    private function filter($list)
    {
        $urls = [];
        foreach ($list as $key => $url) {
            if (count($urls) >= $this->limit) {
                break;
            }
            if ($this->validate($url) && !in_array($url, $urls) && $this->getContent($url) != "") {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    private function validate($url)
    {
        $valid = true;

        if (strpos($url, substr($this->host, 8)) === false || strpos($url, ' ') !== false
            || $this->getRespCode($url) != 200
        ) {
            return false;
        }

        foreach ($this->ignore_list as $val) {
            if (stripos($url, $val) !== false) {
                $valid = false;
                break;
            }
        }

        return $valid;
    }

    public function updateFiles()
    {
        file_put_contents('data/index.txt', implode("\n", $this->host_paths));

        for ($i = 0; $i < $this->limit; $i++) {
            file_put_contents('data/pages/' . ($i + 1) . '.txt', strip_tags($this->getContent($this->host_paths[$i])));
        }
    }

    private function getContent($url)
    {
        $content = preg_replace("'<script[^>]*?>.*?</script>'si", "", file_get_contents($url));
        $content = preg_replace("'<style[^>]*?>.*?</style>'si", "", $content);
        $content = str_replace("\n", " ", $content);
        return preg_replace("/(\s){2,}/u", " ", $content);
    }

    public function getLemmatizedFile($filepath)
    {
        return explode("\t", file_get_contents($filepath));
    }

    private function getWords($filepath)
    {
        if (preg_match_all("/\b(\w+)\b/ui", file_get_contents($filepath), $matches)) {
            foreach ($matches[1] as $key => $word) {
                $tmp = mb_strtoupper($word);
                $matches[1][$key] = iconv('utf-8', 'windows-1251//IGNORE', $tmp);
            }

            return $matches[1];
        }

        return [];
    }

    private function saveLemmatizedFile($words, $filepath)
    {
        $str = implode("\t", $words);
        if (strpos($str, "\t") === 0) {
            $str = substr($str, 1);
        }
        if (strrpos($str, "\t") === strlen($str) - 1) {
            $str = substr($str, 0, strlen($str) - 1);
        }
        while (strpos($str, "\t\t") !== false) {
            $str = str_replace("\t\t", "\t", $str);
        }

        file_put_contents($filepath, strtolower($str));
    }

    public function lemmatizeFiles(phpMorphy $morphy)
    {
        for ($i = 1; $i <= $this->limit; $i++) {
            $filename = $i .'.txt';
            $words = $this->getWords('data/pages/'.$filename);
            $result = [];
            foreach ($words as $key => $word) {
                if ($morphy->findWord($word)) {
                    $result[] = mb_convert_case($morphy->lemmatize($word)[0], MB_CASE_LOWER, "windows-1251");
                }
            }

            $this->saveLemmatizedFile($result, 'data/lemmatized/'.$filename);
        }
    }
}
