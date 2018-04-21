<?php

header("content-type: application/javascript");

error_reporting(E_ALL);
ini_set('display_errors', 1);



/*
    API connection to exchanges
    work in progress
    Can check for ads and apply waterfall
    pass the json encoded ads into
    callback further down

require 'exchanges/mobfox.php';
require 'exchanges/bizrate.php';
require 'exchanges/smaato.php';

*/

$publishers = json_decode(file_get_contents('sites.json'));
$pubid =isset($_GET['p']) ? $_GET['p'] : 'N';

if(isset($_GET['u'])){
    $refererurl = $_GET['u'];
}
else{
    $refererurl = '';
}

/*
* Validate the site
*/
$found = false;

foreach($publishers as $k=>$v)
{
    if($v->siteID == $pubid){
        $found = $v;
    }
}


if($found === false){
    exit("console.log('nxt error: site not found');");
}

/*
* Get rough user qualities
*/
$ip = $_SERVER['REMOTE_ADDR'];
$useragent = urlencode($_SERVER['HTTP_USER_AGENT']);
$refer = (!isset($_SERVER['HTTP_REFERER'])) ? '' : urlencode($_SERVER['HTTP_REFERER']);
?>

var checkNxtExists = setInterval(function() {
if (typeof nxtMedia !== 'undefined') {
       nxtMedia.callback();
       console.log("Exists!");
       clearInterval(checkNxtExists);
   }
}, 100);
