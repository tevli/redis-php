<?php
error_reporting(E_ALL);

echo "Logs from your program will appear here";

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($sock, SOL_SOCKET, SO_REUSEPORT, 1);
socket_bind($sock, "localhost", 6379);
socket_listen($sock, 5);

$clients = array($sock);

$values = [];

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
   $response = _handle($message,$values);
   @socket_write($r, $response, strlen($response));
  }
}

socket_close($accept);

function _handle($message,&$values=[]): string
{
    if(!empty($message)) {
        var_dump('1-message is '.$message);
            $message = _unserialize($message);

            var_dump('2-message is '.$message);
            //try to split message to determine if set or get.
            $spl = explode(' ',$message);
            var_dump('spl[0] is '.$spl[0]);
            var_dump('spl[1] is '.$spl[1]);
            var_dump('spl[2] is '.$spl[2]);
            if(!empty($spl[1])){
                switch (strtolower($spl[0])){
                    case 'set':
                        if((!empty($spl[3]))&&(strtolower($spl[3])=='px')){
                            var_dump('spl[3] is '.$spl[3]);
                            var_dump('spl[4] is '.$spl[4]);
                            //the message comes with an expiry date.
                            $values[$spl[1]]['exp'] = is_numeric($spl[4]) ? $spl[4] : 0;
                            $values[$spl[1]]['exp_time'] = milliseconds();
                        }
                        $values[$spl[1]]['value'] = $spl[2];
                        var_dump('setting   ');
                        print_r($values);
                        return _resp_format('OK');
                    case 'get':
                        var_dump('we are now in the getting  ');
                        print_r($values);
                        if(isset($values[$spl[1]]['exp_time'])){
                            $exp_time = $values[$spl[1]]['exp_time'];
                            $exp = $values[$spl[1]]['exp'];
                            print_r(['time'=>milliseconds(),'exp_time'=>$exp_time,'diff'=>(milliseconds() - $exp_time),'exp'=>$exp]);
                            if((milliseconds() - $exp_time)>=$exp){
                                return _resp_format(NULL);
                            }
                        }
                        return _resp_format($values[$spl[1]]['value']??$spl[1]);
                    case 'echo':
                            return _resp_format($spl[1]);
                    default:
                        return _resp_format('PONG');

                }
            }
            else{
                if(strpos($message,'echo')){
                    return _resp_format($message);
                }
                return _resp_format('PONG');
            }

    }
    return _resp_format('PONG');
}

function _resp_format($value,$num=0){
    var_dump('value before _clean =>'.$value);
    if((!is_numeric($value))&&(!is_null($value))) {
        $value = _clean($value);
    }
    var_dump('value after _clean =>'.$value);
    if(is_null($value)){
        //null value, so bypass other procedures and return early.
        return "$-1\r\n";
    }
    if(empty($value)){
        //empty array RESP string, bypass and return early.
        return "0\r\n";
    }
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
    $retval.=$value."\r\n";
    var_dump('retval at this point is '.$retval);


    return $retval;
}

function _clean($message){
    $sacred_words = ['echo','get','set','ping'];
    var_dump('message at this point is '.$message);
    foreach($sacred_words as $sacred_word){
        $message = str_replace($sacred_word,'',$message);
    }
    var_dump('message at this second point is '.$message);
    return preg_replace('/[^A-Za-z\-]/', '', $message);
}

function _unserialize($message){

    var_dump('message passed to _unserialize is =>'.$message);
    $words = '';
    $forbidden = ['*','$'];

       for ($i=0;$i<strlen($message);$i++){
           if(!is_numeric($message[$i])){
               if(!in_array($message[$i],$forbidden)){
                   $words.=$message[$i];
               }
           }
           else{
               if(!in_array($message[$i-1],$forbidden)){
                   //check if predecessor is a forbidden word.
                   $words.=$message[$i];
               }
           }
       }
       $words = str_replace("\r\n",' ',$words);

    //iterate over words to remove double and triple paddings.
    $main_words = '';
    for ($e=0;$e<strlen($words);$e++){
        if($words[$e]==' ') {
            if ($words[$e - 1] != ' ') {
                $main_words .= $words[$e];
            }
        }
        else{
            $main_words .=$words[$e];
        }
    }

       var_dump("main_words here is=>$main_words");
       return $main_words;
//    $message = preg_replace('/[^A-Za-z0-9\-]/', '', $message);
}


function milliseconds(): int
{
    $mt = explode(' ', microtime());
    return intval( $mt[1] * 1E3 ) + intval( round( $mt[0] * 1E3 ) );
}
?>
