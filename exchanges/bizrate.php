<?php

    class network_bizrate 
    {
        private $request_url;

        public function __construct($array)
        {
            $url = "https://engine.nxt.media/bizrate/index_json.php?p=";
            $url .= $array['placement'] . '&l=' . $_SERVER['REMOTE_ADDR'];
            $url .= '&k=' . $array['keywords'] . '&c=' . $array['category']; 

            $this->request_url = $url;
        }

        private function buildResponse()
        {            
            $ad = json_decode(file_get_contents($this->request_url));

            if(isset($ad->error)){
                return false;
            }
            else
            {
                if(isset($ad->request->htmlString)){
                    $json = array(
                        "request" => array("htmlString" => $ad->request->htmlString)
                    );
                    return json_encode($json);
                }
                return false;
            }                     
        }

        public function getAd(){            
            return $this->buildResponse();
        }

    }

?>