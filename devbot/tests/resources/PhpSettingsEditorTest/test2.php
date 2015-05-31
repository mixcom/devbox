<?php
for ($i = 1; $i <= 2; $i++) {
    define('testConstant' . $i, 'constant' . $i);
}

$vars = [];
for ($i = 1; $i <= 3; $i++) {
    $vars['testVariable' . $i] = $i;
}
extract($vars);
unset ($vars);
unset ($i);
