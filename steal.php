<?php

/*

  connects to a hue bridge and begins the touchlink operation.


  yes, this PHP code could be a lot better.  I wrote it for me in about 90 min.
  PR's gladly accepted.

  written / used on OSX sierra and php, but probably works on php 7 / ubuntu
  probably not on windows, but probably only a little bit of work to make work

  should work w/ *nix / bash environments on widows 10, though.
  
  php -v
    PHP 5.6.30 (cli) (built: Feb  7 2017 16:18:37)
    Copyright (c) 1997-2016 The PHP Group
    Zend Engine v2.6.0, Copyright (c) 1998-2016 Zend Technologies

*/
function log_msg($string=''){
  echo("\n".$string."\n");
}

function get_hue_bridges() {

  $url = 'https://www.meethue.com/api/nupnp';

  $bridges_json_string = do_api_call($url);

  $bridges = json_decode($bridges_json_string['content'], true);

  /*

    $bridges will look like:

    array(1) {
      [0]=>
        object(stdClass)#1 (2) {
          ["id"]=>
            string(16) "001788fffefffff"
          ["internalipaddress"]=>
            string(12) "192.168.10.8"
        }
      }

  */

  log_msg();
  foreach ($bridges as $index => $tuple) {
    $string="Bridge [$index] is at IP: [".$tuple['internalipaddress']."]";
    log_msg($string);

  }

}

function get_client_token($ip) {

  $url = "http://$ip/api";

  // We need to wait for the user to push the bridge button before we phone home
  log_msg('press the link button on your bridge and then hit enter. Bridge: ' . $url);
  fgets( STDIN );

  $data = array(
    'devicetype' => 'HueLights#API'
  );

  $result = do_api_call($url, $data ,'POST');

  $client_json = json_decode($result['content'],true);

  $client_id = $client_json[0]['success']['username'];

  log_msg('You now have client ['.$client_id.'] on bridge ['.$ip.']');

}


function start_bulb_steal($ip, $client_id) {

  $url = "http://$ip/api/$client_id/config";

  // We need to wait for the user to push the bridge button before we phone home
  log_msg('Make sure the light bulb is on and no more than 6 inches from the bridge and then press enter');
  fgets( STDIN );

  $data = array(
    'touchlink' => true
  );

  $result = do_api_call($url, $data ,'PUT');

  $status = json_decode($result['content'],true);

  if (isset($status[0]['success'])) {
    log_msg('Wait for light to blink. Repeat steal process for each light and then search for new lights in the app.');
  } else {
    log_msg('something went wrong.  Sorry!');
    var_dump($status);
  }

}


function do_api_call($url, $params=array(), $method='GET') {

  $retuest_method = array(

    // Default is GET
    CURLOPT_HTTPGET => ($method == 'GET')? true:false,

    //some API calls to Scoot are POST (auth... to prevent sensitve info from making it into access logs)
    CURLOPT_POST => ($method == 'POST')? true:false

  );

  $curl_options = array(

      // true = capture output as php object, not cli out to stderr
      CURLOPT_RETURNTRANSFER => true,

      // return headers in addition to content
      CURLOPT_HEADER         => true,

      // follow redirects
      CURLOPT_FOLLOWLOCATION => true,

      // handle all encodings
      CURLOPT_ENCODING       => "",

      // set referer on redirect
      CURLOPT_AUTOREFERER    => true,

      // timeout on connect
      CURLOPT_CONNECTTIMEOUT => 10,

      // timeout on response
      CURLOPT_TIMEOUT        => 10,

      // stop after 10 redirects
      CURLOPT_MAXREDIRS      => 2,

      CURLINFO_HEADER_OUT    => true,

      // Disabled SSL Cert checks; almost never a good idea to turn this off
      CURLOPT_SSL_VERIFYPEER => true,

      // PHP's cURL API is littered w/ versions
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1

  );

  // init libCurl
  $ch      = curl_init( $url );
  curl_setopt_array( $ch, $curl_options );

  // Do we want to GET or POST?
  curl_setopt_array( $ch, $retuest_method );

  // add our sauce for hue's api
  //TODO: only supply POSTFILEDS when request is of type POST


  if ($method == 'POST') {
    curl_setopt_array( $ch, array(CURLOPT_POSTFIELDS => json_encode($params)) );
  } elseif ($method == 'PUT') {
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($params));
  }


  $rough_content = curl_exec( $ch );
  $err     = curl_errno( $ch );
  $errmsg  = curl_error( $ch );
  $header  = curl_getinfo( $ch );
  curl_close( $ch );

  // take headers out of body
  $header_content = substr($rough_content, 0, $header['header_size']);

  // Little bit of work to get the cookies string
  $body_content = trim(str_replace($header_content, '', $rough_content));

  // Set up return payload
  $header['errno']   = $err;
  $header['errmsg']  = $errmsg;
  $header['content'] = $body_content;

  return $header;
}





// $argv[0] will always be the script name.  $argv[1] should be the command
switch ($argv[1]){
  case 'bridges':
    get_hue_bridges();
  break;

  case 'client':
    $brige_ip = $argv[2];
    get_client_token($brige_ip);
  break;

  case 'steal':
    $brige_ip = $argv[2];
    $client = $argv[3];
    start_bulb_steal($brige_ip, $client);
  break;

  default:
    log_msg('You need to specify a command and either an IP or an IP and a client token.  See readme.md');
  break;
}
