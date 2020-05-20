<?php
class Secrets {
    private $secrets;

    function __construct(string $secretsFile) {
        foreach(json_decode(file_get_contents(get_include_path() . '/' . $secretsFile)) as $key => $value) {
            $this->$key = $value;
        }
    }
}
