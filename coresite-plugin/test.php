<?php
#$a = 2, $b = 3;
#echo sprintf ('%dx%d', 23, 32);

$key = 'key:test';
$colon = strpos ($key, ':');

echo substr ($key, 0, $colon);
?>
