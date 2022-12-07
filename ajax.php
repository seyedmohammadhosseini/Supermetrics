<?php

require_once(__DIR__ . '/services/Supermetrics.php');

set_exception_handler(function ($ex) {
    error_log($ex->getMessage() . "\nStack trace:\n" . $ex->getFile() . '(' . $ex->getLine() . ")\n" . $ex->getTraceAsString());

    header('x-supermetrics-error: ' . $ex->getMessage(), true, 500);
    header('content-type: text/plain');
    echo $ex->getMessage() . "\nStack trace:\n" . $ex->getFile() . '(' . $ex->getLine() . ")\n" . $ex->getTraceAsString();
});

// make sure that the request is being made via Ajax
if ($_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    throw new \Exception('missing x-requested-with header');
}

$inputData = file_get_contents('php://input');

if ($inputData == '') {
    $requestBody = [];
} else {
    $requestBody = json_decode($inputData, true);

    if (json_last_error()) {
        throw new \Exception(json_last_error_msg());
    }
}

if (!preg_match('/(Ajax|Api)$/', $_GET['service'])) {
    throw new \Exception('service not allowed via ajax: ' . $_GET['service']);
}

try {
    $instance = \Supermetrics::get($_GET['service']);
} catch (\Exception $e) {
    throw new \Exception('service not allowed via ajax: ' . $_GET['service']);
}

if (!method_exists($instance, $_GET['method'])) {
    throw new \Exception('method ' . $_GET['method'] . ' does not exist on service ' . $_GET['service']);
}

$result = call_user_func_array([$instance, $_GET['method']], [$requestBody, true]);

if (is_null($result)) {
    $result = ['ok' => true];
}

$encoded = json_encode($result);

if (json_last_error()) {
    throw new \Exception('error encoding JSON response: ' . json_last_error_msg());
}

header('content-type: application/json');
echo $encoded;
exit;
