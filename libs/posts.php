<?php
class post {
    public $title;
    public $created;
    public $lastModified;
    public $filename;
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
        $matches = array();
        if(!preg_match('/^([0-9]{8})\-(.*)\.[^\.]+$/', $file, $matches)) {
            continue;
        }
        $created = DateTimeImmutable::createFromFormat('Ymd', $matches[1]);
        if($created === FALSE) {
            throw new Exception("File $file has invalid filename - cannot get date");
        }
        $lastModified = filemtime("$dir/$file");
        if($lastModified === FALSE) {
            throw new Exception("Cannot get last modified of $file");
        }

        $post = new post();
        $post->created = $created;
        $post->title = $matches[2];
        $post->lastModified = new DateTimeImmutable("@$lastModified");
        $post->filename = $file;
        $ret[] = $post;
    }

    return $ret;
}
