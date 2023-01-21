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

$clients = array($sock);

while(true) {
 socket_read($accept, 2048 );
 $response = "PONG\r\n";
// foreach($clients as $client) {
//  socket_write($accept, $response, strlen($response));
// }
     $read = $clients;
     if (socket_select($read, $write, $e, 0) < 1) continue;
    if (in_array($sock, $read)) {
          $clients[] = $client = socket_accept($sock);
          $key = array_search($sock, $read);
          unset($read[$key]);
    }
    foreach ($read as $readSock) {
          $data = socket_read($readSock, 2048);
          socket_write($readSock, "PONG\r\n");
      }

}

socket_close($accept);
?>
