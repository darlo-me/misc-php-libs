<?php
function pretty_print($str, $n=1): string {
    return preg_replace('/^/m', str_pad("", $n, "\t"), $str);
}
