<?php
require_once('libs/Secrets.php');
class Config {
	public $secrets;

	function __construct() {
		$this->secrets = new Secrets('conf/secrets.json');
	}
}
