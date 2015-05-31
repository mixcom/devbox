<?php
echo 'test output';

function aap() {
    return 'test';
}
define('SITE_ROOT', 'aap');
define(
    'SCHAAP',
    aap()
);


$blaat = array (
  'a' => 'aa',
  'b' => 'bb',
  'c' => array (
      'c', 'cc', 'ccc'
  )
);

$base_url = 'http://test';
