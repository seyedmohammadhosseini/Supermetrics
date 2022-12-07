<?php

require_once(__DIR__ . '/services/Supermetrics.php');

$environmentService = \Supermetrics::get('EnvironmentService');
if (empty($environmentService->getEnvironmentSetting('hostname', 'database'))) {
    throw new \Exception('environment.cfg is missing value for hostname.database. Make sure environment.cfg has been set up.');
}

// A very simple SEO url.
$urlMapping = [
    'index' => 'index.html',
    'form'  => 'form.html',
];

$requestedRoute = !empty($_REQUEST['route']) ? strtolower(trim($_REQUEST['route'])) : 'index';
$pageFullPath   = !empty($urlMapping[$requestedRoute]) ? $urlMapping[$requestedRoute] : 'error.html';

require_once(__DIR__ . "/pages/{$pageFullPath}");
