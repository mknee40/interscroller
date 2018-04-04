<?php

class network_mobfox 
{
    private $request_url;

    public function __construct($array)
    {
        $url = 'https://my.mobfox.com/request.php?rt=api&r_type=banner&adspace_width=320&adspace_height=480&';
        $url .= 'i='.$array['ip'].'&';
        $url .= 's='.$array['hash'].'&';
        $url .= 'adspace_strict=1&';
        $url .= 'r_floor=0.8&';
        $url .= 'u='.$array['useragent'].'&';
        $url .= 'imp_instl=1&imp_secure=1&';
        $url .= 's_subid=' . $array['publisher'] . '&';
        $url .= 'p=' . $array['refer'] . '&';
        $url .= 'r_resp=json';
        $this->request_url = $url;
    }

    private function buildResponse()
    {
        $request = file_get_contents($this->request_url);
        $check = json_decode($request);
        if(isset($check->error)){
            return false;
        }else{
            return $request;
        }
    }

    public function getAd(){            
        //return $this->buildResponse();
        return $this->request_url;
    }
}


    


?>