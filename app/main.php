<?php
error_reporting(E_ALL);

// You can use print statements as follows for debugging, they'll be visible when running tests.
echo "Logs from your program will appear here";

// Uncomment this to pass the first stage
$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($sock, SOL_SOCKET, SO_REUSEPORT, 1);
socket_bind($sock, "localhost", 6379);
socket_listen($sock, 5);
$accept = socket_accept($sock); // Wait for first client

while(true) {
 $buf = socket_read($accept, 2048, PHP_NORMAL_READ);
 $response = "+PONG\r\n";
 socket_write($accept, $response, 9);
}

socket_close($accept);
?>
