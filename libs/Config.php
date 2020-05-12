<?php
require_once('libs/Secrets.php');
class Config {
	public $secrets;

	function __construct(string $secretsPath) {
		$this->secrets = new Secrets($secretsPath);
	}
}
