<?php

function redirect($to){
    echo "<html><head>
        <meta http-equiv='refresh' content='2; URL=".$to."'>
        <meta name='keywords' content='automatic redirection'>
    </head>
    <body> If your browser doesn't automatically go to the Installation within a few seconds,
    you may want to go to <a href='".$to."'>the destination</a> manually.
    </body></html>";
    die();
}

function get_url($request_url) {

    $url = $_SERVER['REQUEST_URI']; //returns the current URL
    $parts = explode('/',$url);
    $dir = $_SERVER['SERVER_NAME'];
    for ($i = 0; $i < count($parts) - 1; $i++) {
     $dir .= $parts[$i] . "/";
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $dir.$request_url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

if( get_url( "preinstall") == "1" ){
    redirect("install");
}
if( get_url( "index.php/preinstall") == "1" ){

}

?>
