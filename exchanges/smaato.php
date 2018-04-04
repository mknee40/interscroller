<?php

/*
*   ---- Video ----

*   http://soma.smaato.net/oapi/reqAd.jsp?
    adspace=130283051&apiver=502&format=video&formatstrict=true&height=480&pub=1100033462&
    response=XML&vastver=2&videotype=outstream&width=320
*
*/

    class network_smaato 
    {
        private $request_url;
        public $settings;
        public $error;
        public $banner;
        private $headers;

        public function __construct($array)
        {
            $url = "http://soma.smaato.net/oapi/reqAd.jsp?";
            $url .= "adspace=".$array['adspace']."&";
            $url .= "pub=".$array['pub']."&";
            $url .= "devip=".$array['ip']."&";
            $url .= "device=".$array['useragent'] . "&";
            $url .= "format=".$array['format']['type']."&";
            $url .= "dimension=full_320x480&dimensionstrict=true&";
            $url .= "ref=" . $array['refer'] . "&";
            $url .= "age=25&gender=m&";
            $url .= "response=".$array['format']['resp'];
            $this->request_url = $url;

            $this->headers = $array['headers'];
            
        }

        function mkcurlreq(){
            $headers = [];
            $ch = curl_init($this->request_url);                                                                                
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
            curl_setopt($ch, CURLOPT_HEADERFUNCTION,
                function($curl, $header) use (&$headers)
                {
                    $len = strlen($header);
                    $header = explode(':', $header, 2);
                    if (count($header) < 2) // ignore invalid headers
                    return $len;

                    $name = strtolower(trim($header[0]));
                    if (!array_key_exists($name, $headers))
                    $headers[$name] = [trim($header[1])];
                    else
                    $headers[$name][] = trim($header[1]);

                    return $len;
                }
            );                                                                  
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);                                                                      
            $result = curl_exec($ch);
            curl_close ($ch);
            if(isset($headers['somaerror'])){
                return false;
            }else{
			    return $result;
            }
        }

        private function buildResponse()
        {
			$ad = $this->mkcurlreq();
            
            if($ad){	
                return $ad;
            }else{
                return false;
            }
        }

        public function getAd(){            
            return $this->buildResponse();
        }

    }




?>