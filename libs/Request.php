<?php
class Request {
    public $server;

    public $files;
    public $post;

    public $get;

	function __construct(&$server, &$files, &$post, &$get) {
		$this->server = $server;
		$this->files = $files;
		$this->post = $post;
		$this->get = $get;
	}
}
