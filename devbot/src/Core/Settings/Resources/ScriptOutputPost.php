<?php
while (ob_get_level() > 0) {
    ob_end_clean();
}

$__constants = get_defined_constants(true);
$__userConstants = isset ($__constants['user']) ? $__constants['user'] : [];
$__variables = get_defined_vars();
$__variables = array_diff_key($__variables, $__preVariables);
unset ($__variables['__variables']);
unset ($__variables['__preVariables']);
unset ($__variables['__constants']);
unset ($__variables['__userConstants']);

echo json_encode([
    'constants' => $__userConstants,
    'variables' => $__variables,
]);
