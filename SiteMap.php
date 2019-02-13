<?php

class SiteMap
{
    public $host;
    public $host_paths;
    public $ignore_list;
    public $limit;

    public function __construct($host, $ignore_list)
    {
        $this->host = $host;
        $this->limit = 100;
        $this->ignore_list = $ignore_list;
        $this->host_paths = $this->filter($this->findPages($this->host));
    }

    private function getRespCode($url)
    {
        return substr(get_headers($url)[0], 9, 3);
    }

    private function findPages($page)
    {
        $content = file_get_contents($page);
        preg_match_all("/<a[^>]*href\s*=\s*'([^']*)'|".'<a[^>]*href\s*=\s*"([^"]*)"'."/is", $content, $match);

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

    private function validate($url){
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

    public function updateListFile()
    {
        $content = '';
        foreach ($this->host_paths as $path) {
            $content .= $path . "\n";
        }

        file_put_contents('index.txt', $content);
    }

    public function updateFilesContent()
    {
        for ($i = 0; $i < $this->limit; $i++) {
            file_put_contents('data/'.($i + 1).'.txt', strip_tags($this->getContent($this->host_paths[$i])));
        }
    }

    public function getContent($url)
    {
        $content = preg_replace("'<script[^>]*?>.*?</script>'si","", file_get_contents($url));
        $content = preg_replace("'<style[^>]*?>.*?</style>'si","", $content);
        $content = str_replace("\n", " ", $content);
        return preg_replace("/(\s){2,}/u"," ",$content);
    }

}