<?php

$string = 'CACHED_FRONT_FORM_KEY=tu20VrdJKlAeIKGp;';
foreach (explode(';', $string) as $str) {
    if (strlen($str) > 0) {
        $str = trim($str);
        var_dump($str);
    }
}

