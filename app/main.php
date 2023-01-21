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
 $response = "+PONG\r\n";

 if(socket_select($clients,$write,$e,0)<1) continue;
    if (in_array($sock, $clients)) {
         $clients[] = $client = socket_accept($sock);
         $key = array_search($sock, $clients);
         unset($clients[$key]);
   }
 foreach($clients as $client) {
  var_dump($client);
  socket_read($client,2048);
  socket_write($accept, $response, strlen($response));
 }
}

socket_close($accept);
?>
