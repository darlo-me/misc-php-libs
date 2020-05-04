<?php
class Session {
    function __construct(string $sessionId, array &$sessionData) {
        $this->id = $sessionId;
        $this->data = $sessionData;
    }
}
