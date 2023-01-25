<?php
error_reporting(E_ALL);

echo "Logs from your program will appear here";

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($sock, SOL_SOCKET, SO_REUSEPORT, 1);
socket_bind($sock, "localhost", 6379);
socket_listen($sock, 5);

$clients = array($sock);

while(true) {
    $values = [];

 $read =  $clients;
 if(socket_select($read,$write,$e,0)<1) continue;
    if (in_array($sock, $read)) {
         $clients[] = $client = socket_accept($sock);
         $key = array_search($sock, $clients);
         unset($read[$key]);
   }
  foreach($read as $r) {
   $message = @socket_read($r,2048);
   $response = _handle($message,$values);
   @socket_write($r, $response, strlen($response));
  }
}

socket_close($accept);

function _handle($message,$values=[]): string
{
    if(!empty($message)) {
            //try to split message to determine if set or get.
            $spl = explode(' ',$message);
            if(!empty($spl[1])){
                switch (strtolower($spl[0])){
                    case 'set':
                        $values[$spl[1]] = $spl[2];
                        return _resp_format('OK');
                    case 'get':
                        return _resp_format($values[$spl[1]]);
                    case 'echo':
                            return _resp_format($spl[1]);
                    default:
                        return _resp_format('PONG');

                }
            }
            else{
                return _resp_format('PONG');
            }

    }
    return _resp_format('PONG');
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
