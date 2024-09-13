<?php

require __DIR__ . '/..//libs/composer/vendor/autoload.php';

$crondaemon = new \ecoaragonapp\common\controller();
print_r( $crondaemon->crondaemon() );
