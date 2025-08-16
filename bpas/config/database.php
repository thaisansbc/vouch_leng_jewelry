<?php
defined('BASEPATH') or exit('No direct script access allowed');

$active_group  = 'default';
$query_builder = true;

$db['default'] = [
    'dsn'          => '',
    'hostname'     => 'localhost',
    'username'     => 'root',
    'password'     => '12345678',
    'database'     => 'vouch_leng_jewelry_db',
    'dbdriver'     => 'mysqli',
    'dbprefix'     => 'bpas_',
    'pconnect'     => false,
    'db_debug'     => true,
    'cache_on'     => false,
    'cachedir'     => '',
    'char_set'     => 'utf8',
    'dbcollat'     => 'utf8_general_ci',
    'swap_pre'     => '',
    'encrypt'      => false,
    'compress'     => false,
    'stricton'     => false,
    'failover'     => [],
    'save_queries' => false,
];