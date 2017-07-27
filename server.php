<?php
require_once "helpers.php";
mb_internal_encoding("UTF-8");

global $spawn;

declare(ticks=1);

pcntl_signal(SIGINT, "sig_handler");
pcntl_signal(SIGTERM, "sig_handler");
pcntl_signal(SIGHUP, "sig_handler");

$db_data = getTags();
$db_tags = $db_data['db_tags'];
$db_tags_ids = $db_data['db_tags_ids'];

set_time_limit(0);

// Create socket
$socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
// Bind socket to port
$result = socket_bind($socket, HOST, PORT) or die("Could not bind to socket\n");
// Start listening for connections
$result = socket_listen($socket, 3) or die("Could not set up socket listener\n");

// Read client input
for (; ;) {
    // Accept incoming connections
    // Spawn another socket to handle communication
    $spawn = socket_accept($socket) or die("Could not accept incoming connection\n");

    $json_input = socket_read($spawn, 102400) or $json_input = "";

    $time1 = $json_input ? microtime(true) : 0;

    $json_input = utf8_decode($json_input);
    $response = json_decode($json_input);

    $action = $response->action;
    $parameters = array();

    switch ($action) {
        case "refresh":
            $db_data = getTags();
            $db_tags = $db_data['db_tags'];
            $db_tags_ids = $db_data['db_tags_ids'];
            break;
        default:
            $string = $response->string;
            $tags = suggestTags($string, $db_tags, $db_tags_ids);
            $parameters['tags'] = $tags;
    }

    $time2 = $json_input ? microtime(true) : 0;
    $parameters['execution'] = (float)number_format($time2 - $time1, '2');

    $output = json_encode($parameters);
    socket_write($spawn, $output, mb_strlen($output)) or die("Could not write output\n");
}