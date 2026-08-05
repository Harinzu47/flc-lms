<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$fks = \Illuminate\Support\Facades\Schema::getForeignKeys('xp_logs');
echo json_encode($fks, JSON_PRETTY_PRINT);
