<?php
class post {
    public $title;
    public $created;
    public $lastModified;
    public $filename;

    function __construct(string $path) {
        $file = basename($path);
        if(!preg_match('/^([0-9]{8})\-(.*)\.[^\.]+$/', $file, $matches)) {
            throw new InvalidArgumentException("File $file has invalid filename");
        }
        $created = DateTimeImmutable::createFromFormat('Ymd', $matches[1]);
        if($created === FALSE) {
            throw new InvalidArgumentException("File $file has invalid filename - cannot get date");
        }
        $lastModified = filemtime($path);
        if($lastModified === FALSE) {
            throw new RuntimeException("Cannot get last modified of $file");
        }

        $this->created = $created;
        $this->title = $matches[2];
        $this->lastModified = new DateTimeImmutable("@$lastModified");
        $this->filename = $file;
    }
}
/*
 * return an array of post
 */
function getPosts(string $dir, int $max = 10, string $last = null): array {
    $posts = array();
    $files = scandir($dir, SCANDIR_SORT_DESCENDING);
    if($files === FALSE) {
        throw new Exception("Cannot get content of $dir");
    }
    $ret = array();
    foreach($files as $file) {
        if($last !== null) {
            if($last === $file) {
                $last = null;
            }
            continue;
        }
        if($file === '.' || $file === '..') {
            continue;
        }
        if(count($ret) >= $max) {
            break;
        }
        try {
            $ret[] = new post("$dir/$file");
        } catch(InvalidArgumentException $e) {}
    }

    return $ret;
}
