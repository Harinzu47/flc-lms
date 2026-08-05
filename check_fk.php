<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Schema;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$fks = Schema::getForeignKeys('xp_logs');
echo json_encode($fks, JSON_PRETTY_PRINT);
