<?php
error_reporting(E_ALL);

echo "Logs from your program will appear here";

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($sock, SOL_SOCKET, SO_REUSEPORT, 1);
socket_bind($sock, "localhost", 6379);
socket_listen($sock, 5);

$clients = array($sock);

while(true) {

 $read =  $clients;
 if(socket_select($read,$write,$e,0)<1) continue;
    if (in_array($sock, $read)) {
         $clients[] = $client = socket_accept($sock);
         $key = array_search($sock, $clients);
         unset($read[$key]);
   }
  foreach($read as $r) {
   $message = @socket_read($r,2048);
   @socket_write($r, _echo($message), strlen(_echo($message)));
  }
}

socket_close($accept);

function _echo($message): string
{
    if(!empty($message)) {

            $val = preg_replace('/[^A-Za-z\-]/', '', $message);
            if($val!='ping'){
                return _resp_format(str_replace('echo','',$val));
            }

    }
    return "+PONG\r\n";
}

function _resp_format($value,$num=0){
    $length = strlen($value);
    $retval = '';
    if($num==0){
        $retval .='+';
    }
    else{
        $retval.='$';
    }
    if(strpos($value,' ')){
        //if value is more than one, we can use recursion, hence the $num=0;
        $num=1;
    }
//    $retval .= $length.'\r\n'.$value;
    $retval.=$value."\r\n";


    return $retval;
}
?>
