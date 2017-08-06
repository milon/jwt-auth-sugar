<?php
require __DIR__ . '/vendor/autoload.php';
use TonicCrm\Tools\JwtApiCredential\JwtApiCredential;


$credentials = [
    'service' => 'tonic-crm-rtc-category',
    'apikey' => 'a62de254-a828-4aaf-b10d-b6e435e08fe2',
    'secret' => '8qHew16ZtvDYOwdAPGsf06n2uYkHrUAp',
];
$jwt = '';
$apiCheck = new JwtApiCredential($jwt, [
    "redis" => [
        'host' => '127.0.0.1',
        'port' => 6379,
    ],
    "database" => [
        'dbname' => 'crm',
        'user' => 'homestead',
        'password' => 'secret',
        'host' => 'localhost',
//        'driver' => 'pdo_pgsql',
        'driver' => 'pdo_mysql',
    ]
]);

$jwt = $apiCheck->generateJwt($credentials);
//var_dump($jwt);

$apiCheck = new JwtApiCredential($jwt, [
    "redis" => [
        'host' => '127.0.0.1',
        'port' => 6379,
    ],
    "database" => [
        'dbname' => 'crm',
        'user' => 'homestead',
        'password' => 'secret',
        'host' => 'localhost',
        'driver' => 'pdo_mysql',
    ]
]);
$payload = $apiCheck->getVerifiedPayload("tonic-crm-rtc-category");
var_dump($payload);
