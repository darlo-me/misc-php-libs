<?php
class HTTPException extends Exception {
    const MESSAGES = [
        400 => 'Bad request',
        404 => 'Not found',
        500 => 'Internal error',
    ];
    public function __construct(int $code, Exception $previous=null) {
        parent::__construct("", $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]\n";
    }
}
