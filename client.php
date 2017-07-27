<?php
require_once "helpers.php";
header("Content-type:application/json;charset=utf-8");

$empty_json = json_encode(array('tags' => array(), 'execution' => 0));

$action = isset($_GET['action']) && $_GET['action'] ? $_GET['action'] : isset($argv[1]) && $argv[1] ? $argv[1] : die($empty_json);
$parameters = array();

switch ($action) {
    case "refresh":
        break;
    default:
        $string = trim($_POST['string']);

        if (!$string)
            die($empty_json);

        $parameters['string'] = $string;
        $action = 'fetch';
}

$parameters['action'] = $action;
$json = json_encode($parameters);
$json = utf8_encode($json);

// Create socket
$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
// Connect to server
$result = socket_connect($socket, HOST, PORT) or die("Could not connect to server\n");
// Send JSON to server
$success = socket_write($socket, $json, strlen($json)) ? true : false;

// Failure to send data to server
if (!$success)
    die($empty_json);

// Get server response
$result = socket_read($socket, 102400) or die("Could not read server response\n");
// Close socket
socket_close($socket);
die($result);