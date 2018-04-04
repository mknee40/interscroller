<?php

/**
 * Smaato Code Snippet PHP
 * Copyright Smaato, Inc., All rights reserved
 * Version 2.0
 * 
 */

class SmaatoSnippet {
   
    const AD_FORMAT_ALL = "all";
    const AD_FORMAT_IMG = "img";
    const AD_FORMAT_RICHMEDIA = "richmedia";
    const AD_FORMAT_TXT = "txt";   
    const RESPONSE_FORMAT_HTML = "html";
    const RESPONSE_FORMAT_XML = "xml";   
            
    private $adspace;
    private $pub;
    private $response = "html";
    private $format = "all";
    private $height;
    private $width;
    private $dimension = "mma";
    private $devip;
    private $device;
    private $dimensionstrict;
    private $formatstrict;
    private $gender;
    private $age;
    private $coppa;
    private $city;
    private $gps;
    private $region;
    private $zip;
    private $iosadid;
    private $iosadtracking;
    private $googleadid;
    private $googlednt;
    private $did;
    private $diddnt;
    private $wpid;
    private $bbid;
    private $ref;
    private $htmlwithoutp;
    private $client = "phpsnip200";
    private $apiver = 500;
    private $timeout = 5000;
    private $result;
    private $mandatoryparams = array('adspace',
                                     'pub');
    private $supportedformats = array('all', 
                                      'img', 
                                      'richmedia', 
                                      'txt');
    private $responseformats = array('html',
                                     'xml');
    private $internalparams = array('timeout',
                                    'result',
                                    'mandatoryparams',
                                    'supportedformats',
                                    'internalparams',
                                    'responseformats');

    /**
     * Constructor - sets default values for device ip and device id
     * 
     * @return void
     */

    public function __construct() {
        $this->setDeviceIp($this->getDefaultDeviceIp());
        $this->setUserAgent($this->getDefaultUserAgent());
    }

    /**
     * Sends an ad requests to Smaato and stores server response inside of @result property.
     * 
     * @return void
     */

    public function requestAd() {
        $this->validateMandatoryParams();
        $request_url = $this->generateRequestURL();
        if (function_exists('curl_version'))
            $this->sendRequestCurl($request_url);
        else
            $this->sendRequest($request_url);
    }

    /**
     * Returns an ad served by Smaato and parses XML response into an object.
     * 
     * @throws Exception if publisher or adspace ID is invalid
     * 
     * @return string|object. Object will be returned in case of XML response.
     */

    public function getAd() {
        if (stristr($this->result, "ad.errorCode=106") || stristr($this->result, "<error><code>106</code>"))
            throw new Exception("Invalid publisher or adspace ID.");
        if ($this->getResponseFormat() == 'xml')
            $this->result = $this->parseXmlResponse();
        return $this->result;
    }

    /**
     * Checks whether ad is available
     * 
     * @return boolean
     */

    public function isAdAvailable() {
        if (!stristr($this->result, "currently no ad available") && $this->result != '' && $this->result != '<p>&nbsp;</p>')
            return true;
        else
            return false;
    }

    /**
     * Sends Request if curl is not available
     * 
     * @param string $request_url contains request url and GET parameters
     * 
     * @return void
     */

    private function sendRequest($request_url) {
        $optionaArr = array(
            'http' => array(
                'method' => 'GET',
                'header' => $this->getHttpHeaders('string'),
                'timeout' => $this->getTimeout() / 1000
            )
        );
        $this->result = file_get_contents($request_url, false, stream_context_create($optionaArr));
    }

    /**
     * Sends Request if curl is available
     * 
     * @param string $request_url contains request url and GET parameters
     * 
     * @return void
     */

    private function sendRequestCurl($request_url) {
        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, $request_url);
        curl_setopt($request, CURLOPT_HTTPHEADER, $this->getHttpHeaders());
        curl_setopt_array($request, array(
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 15,
            CURLOPT_CONNECTTIMEOUT_MS => $this->getTimeout(),
            CURLOPT_RETURNTRANSFER => true
        ));
        curl_setopt($request, CURLINFO_HEADER_OUT, true);
        curl_setopt($request, CURLOPT_VERBOSE, true);
        $this->result = curl_exec($request);
        curl_close($request);
    }

    /**
     * Validates mandatory parameters
     * 
     * @throws InvalidArgumentException if any of mandatory parameters is null
     * 
     * @return boolean
     */

    private function validateMandatoryParams() {
        foreach ($this->mandatoryparams as $param) {
            if ($this->$param === NULL)
                throw new InvalidArgumentException('Parameter "' . $param . '" cannot be null.');
        }
        return true;
    }

    /**
     * Generates request url from parameters array
     * 
     * @return string Returns request urls that contains GET parameters
     */

    private function generateRequestURL() {
        $paramsArr = $this->prepareParams();       
        $url = "http://soma.smaato.net/oapi/reqAd.jsp?";
        foreach ($paramsArr as $key => $val) {
            if ($val !== null)
                $url .= $key . '=' . urlencode($val) . '&';
        }
        return trim($url, '&');
    }

    /**
     * Excludes internal parameters from request url
     * 
     * @return array Returns array with parameters needed for request url
     */

    private function prepareParams() {      
        if($this->getAdvertisingId() !== null && $this->getUserAgent() !== null) {
            $this->processDeviceId();
        }     
        
        $paramsArr = get_object_vars($this);       
        
        foreach ($this->internalparams as $param) {
            unset($paramsArr[$param]);
        }
        
        return $paramsArr;
    }
    
    /**
     * Sets device ID parameter depending on the device user agent
     * 
     * @return void
     */
    
    private function processDeviceId() {   
        
        if(stristr($this->getUserAgent(), "windows")) {
            $this->setWpId($this->getAdvertisingId());
        }
        elseif(stristr($this->getUserAgent(), "blackberry")) {
            $this->setBbId($this->getAdvertisingId());
        }   
        elseif(stristr($this->getUserAgent(), "android")) {
            $this->setGoogleAdId($this->getAdvertisingId());           
            if($this->getAdvertisingIdDoNotTrack() !== null) {
                $this->setGoogleDnt($this->getAdvertisingIdDoNotTrack());
            }
        }
        elseif(stristr($this->getUserAgent(), "apple")) {
            $this->setIosAdId($this->getAdvertisingId());
            if($this->getAdvertisingIdDoNotTrack() !== null) {
                $this->setIosAdTracking($this->getAdvertisingIdDoNotTrack());
            }
        }      
        $this->getAdvertisingId(null);
        $this->getAdvertisingIdDoNotTrack(null);
    }

    /**
     * Gets user agent recieved by server if it was not set by publisher
     * 
     * @return string 
     */

    private function getDefaultUserAgent() {
        $user_agent_array = array(
            'HTTP_X_ORIGINAL_USER_AGENT',
            'HTTP_X_DEVICE_USER_AGENT',
            'HTTP_X_OPERAMINI_PHONE_UA',
            'HTTP_X_BOLT_PHONE_UA',
            'HTTP_X_MOBILE_UA',
            'HTTP_USER_AGENT');
        foreach ($user_agent_array as $val) {
            $user_agent = filter_input(INPUT_SERVER, $val);
            if (!empty($user_agent))
                return $user_agent;
        }
    }

    /**
     * Retruns array with http headers incluading x-forwarded-for, user-agent, referer
     * 
     * @return array
     */

    private function getHttpHeaders($format = 'array') {
        $headersArr = array(
            'X-Forwarded-For: ' . $this->getDeviceIp(),
            'User-Agent: ' . $this->getUserAgent()
        );
        if ($this->getReferrerUrl() != NULL)
            $headersArr[] = 'Referer: ' . $this->getReferrerUrl();

        if ($format == 'string') {
            $headers = "";
            foreach ($headersArr as $key => $val) {
                $headers .= $key . ": " . $val . "\r\n";
            }
            return $headers;
        }

        return $headersArr;
    }

    /**
     * Gets client ip address received by server if it was not served by publisher
     * 
     * @return string 
     */

    private function getDefaultDeviceIp() {
        $device_ip_array = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR');
        foreach ($device_ip_array as $val) {
            $device_ip = filter_input(INPUT_SERVER, $val);
            if (!empty($device_ip))
                return $device_ip;
        }
    }

    /**
     * Parses received XML ad response into an object
     * 
     * @return object
     */

    private function parseXmlResponse() {
        if (!$this->isAdAvailable())
            return "No ad available";
        
        $xmlObject = new SimpleXMLElement($this->result);
        $responseObject = new stdClass();
        $responseObject->type = strtolower($xmlObject->ads->ad['type']);
        $responseObject->rawXml = $this->result;
        switch ($responseObject->type) {
            case "img":
                $responseObject->clickUrl = $xmlObject->ads->ad->action['target'];
                $responseObject->adLink = $xmlObject->ads->ad->link;
                $responseObject->beacons = $xmlObject->ads->ad->beacons->beacon;
                break;

            case "txt":
                $responseObject->clickUrl = $xmlObject->ads->ad->action['target'];
                $responseObject->adText = $xmlObject->ads->ad->adtext;
                $responseObject->beacons = $xmlObject->ads->ad->beacons->beacon;
                break;

            case "richmedia":
                $responseObject->mediaData = $xmlObject->ads->ad->mediadata;
                $responseObject->beacons = $xmlObject->ads->ad->beacons->beacon;
                break;

            default:
                throw new Exception('Unsupported creative format');
                break;
        }

        return $responseObject;
    }

    /**
     * Returns value of client parameter
     * 
     * @return string
     */

    public function getClient() {
        return $this->client;
    }

    /**
     * Returns value of apiver parameter
     * 
     * @return int
     */

    public function getApiVer() {
        return $this->apiver;
    }

    /**
     * Returns value of adspace parameter
     * 
     * @return int
     */

    public function getAdspaceId() {
        return $this->adspace;
    }

    /**
     * Returns value of pub parameter
     * 
     * @return int
     */

    public function getPublisherId() {
        return $this->pub;
    }

    /**
     * Returns value of response parameter
     * 
     * @return string
     */

    public function getResponseFormat() {
        return $this->response;
    }

    /**
     * Returns value of format parameter
     * 
     * @return string
     */

    public function getAdFormat() {
        return $this->format;
    }

    /**
     * Returns value of height parameter
     * 
     * @return int
     */

    public function getHeight() {
        return $this->height;
    }

    /**
     * Returns value of width parameter
     * 
     * @return int
     */

    public function getWidth() {
        return $this->width;
    }

    /**
     * Returns value of dimension parameter
     * 
     * @return string
     */

    public function getDimension() {
        return $this->dimension;
    }

    /**
     * Returns value of devip parameter
     * 
     * @return string
     */

    public function getDeviceIp() {
        return $this->devip;
    }

    /**
     * Returns value of device parameter
     * 
     * @return string
     */

    public function getUserAgent() {
        return $this->device;
    }

    /**
     * Returns value of dimensionstrict parameter
     * 
     * @return boolean
     */

    public function getDimensionStrict() {
        return $this->dimensionstrict;
    }

    /**
     * Returns value of formatstrict parameter
     * 
     * @return boolean
     */

    public function getFormatStrict() {
        return $this->formatstrict;
    }

    /**
     * Returns value of gender parameter
     * 
     * @return string
     */

    public function getGender() {
        return $this->gender;
    }

    /**
     * Returns value of age parameter
     * 
     * @return int
     */

    public function getAge() {
        return $this->age;
    }

    /**
     * Returns value of coppa parameter
     * 
     * @return int
     */

    public function getCoppa() {
        return $this->coppa;
    }

    /**
     * Returns value of city parameter
     * 
     * @return string
     */

    public function getCity() {
        return $this->city;
    }

    /**
     * Returns value of gps parameter
     * 
     * @return string
     */

    public function getGps() {
        return $this->gps;
    }

    /**
     * Returns value of region parameter
     * 
     * @return string
     */

    public function getRegion() {
        return $this->region;
    }

    /**
     * Returns value of zip parameter
     * 
     * @return int
     */

    public function getZip() {
        return $this->zip;
    }

    /**
     * Returns value of iosadid parameter
     * 
     * @return string
     */

    public function getIosAdId() {
        return $this->iosadid;
    }

    /**
     * Returns value of iosadtracking parameter
     * 
     * @return string
     */

    public function getIosAdTracking() {
        return $this->iosadtracking;
    }

    /**
     * Returns value of googleadid parameter
     * 
     * @return string
     */

    public function getGoogleAdId() {
        return $this->googleadid;
    }

    /**
     * Returns value of googlednt parameter
     * 
     * @return string
     */

    public function getGoogleDnt() {
        return $this->googlednt;
    }
    
    /**
     * Returns value of did parameter
     * 
     * @return string
     */

    public function getAdvertisingId() {
        return $this->did;
    }

    /**
     * Returns value of did parameter
     * 
     * @return string
     */

    public function getAdvertisingIdDoNotTrack() {
        return $this->diddnt;
    }
    
     /**
     * Returns value of wpid parameter
     * 
     * @return string
     */

    public function getWpId() {
        return $this->wpid;
    }
    
     /**
     * Returns value of bbid parameter
     * 
     * @return string
     */

    public function getBbId() {
        return $this->bbid;
    }

    /**
     * Returns value of ref parameter
     * 
     * @return string
     */

    public function getReferrerUrl() {
        return $this->ref;
    }

    /**
     * Returns value of htmlwithoutp parameter
     * 
     * @return int
     */

    public function getHtmlWithoutP() {
        return $this->htmlwithoutp;
    }
    
    /**
     * Returns value of timeout parameter
     * 
     * @return int
     */
    
    public function getTimeout() {
        return $this->timeout;
    }

    /**
     * Sets value of client parameter
     * 
     * @param string $client
     * 
     * @return object Returns current object for fluent interface
     */

    public function setClient($client) {
        $this->client = $client;
        return $this;
    }

    /**
     * Sets value of apiver parameter
     * 
     * @param int $apiver
     * 
     * @return object Returns current object for fluent interface
     */

    public function setApiVer($apiver) {
        $this->apiver = $apiver;
        return $this;
    }

    /**
     * Sets value of adspace parameter
     * 
     * @param int $adspace
     * 
     * @return object Returns current object for fluent interface
     */

    public function setAdspaceId($adspace) {
        $this->adspace = $adspace;
        return $this;
    }

    /**
     * Sets value of pub parameter
     * 
     * @param int $pub 
     * 
     * @return object Returns current object for fluent interface
     */

    public function setPublisherId($pub) {
        $this->pub = $pub;
        return $this;
    }

    /**
     * Sets value of response parameter
     * 
     * @param string $response
     * 
     * @return object Returns current object for fluent interface
     */

    public function setResponseFormat($response) {
        if (!in_array($response, $this->responseformats))
            throw new InvalidArgumentException("Parameter \"response\" is not correct. Allowed values: html, xml");
        $this->response = $response;
        return $this;
    }

    /**
     * Sets value of format parameter
     * 
     * @param string $format
     * 
     * @throws InvalidArgumentException if format is not all, img, richmedia or txt
     * 
     * @return object Returns current object for fluent interface
     */

    public function setAdFormat($format) {
        if (!in_array($format, $this->supportedformats))
            throw new InvalidArgumentException("Parameter \"format\" is not correct. Allowed values: all, img, richmedia, txt");
        if ($format != 'all')
            $this->setFormatStrict(true);
        $this->format = $format;
        return $this;
    }

    /**
     * Sets value of height parameter
     * 
     * @param int $height
     * 
     * @return object Returns current object for fluent interface
     */

    public function setHeight($height) {
        $this->height = $height;
        return $this;
    }

    /**
     * Sets value of width parameter
     * 
     * @param int $width
     * 
     * @return object Returns current object for fluent interface
     */

    public function setWidth($width) {
        $this->width = $width;
        return $this;
    }

    /**
     * Sets value of dimension parameter
     * 
     * @param string $dimension
     * 
     * @return object Returns current object for fluent interface
     */

    public function setDimension($dimension) {
        $this->dimension = $dimension;
        if(strtolower($dimension) != 'mma')
            $this->setDimensionStrict(true);
        return $this;
    }

    /**
     * Sets value of devip parameter
     * 
     * @param string $devip
     * 
     * @return object Returns current object for fluent interface
     */

    public function setDeviceIp($devip) {
        $this->devip = $devip;
        return $this;
    }

    /**
     * Sets value of device parameter
     * 
     * @param string $device
     * 
     * @return object Returns current object for fluent interface
     */

    public function setUserAgent($device) {
        $this->device = $device;
        return $this;
    }

    /**
     * Sets value of dimensionstrict parameter
     * 
     * @param boolean|int $dimensionstrict
     * 
     * @return object Returns current object for fluent interface
     */

    public function setDimensionStrict($dimensionstrict) {
        $this->dimensionstrict = $dimensionstrict;
        return $this;
    }

    /**
     * Sets value of formatstrict parameter
     * 
     * @param boolean|int $formatstrict
     * 
     * @return object Returns current object for fluent interface
     */

    public function setFormatStrict($formatstrict) {
        $this->formatstrict = $formatstrict;
        return $this;
    }

    /**
     * Sets value of gender parameter
     * 
     * @param string $gender
     *
     * @throws InvalidArgumentException if gender is not m, f, male or female
     * 
     * @return object Returns current object for fluent interface
     */

    public function setGender($gender) {
        if (!in_array($gender, array('m', 'f', 'male', 'female')))
            throw new InvalidArgumentException("Parameter \"gender\" is not valid. Allowed values: m, f, male, female");
        $this->gender = $gender;
        return $this;
    }

    /**
     * Sets value of gender parameter
     * 
     * @param string $age
     *
     * @throws InvalidArgumentException if age is not 2 digit number
     * 
     * @return object Returns current object for fluent interface
     */

    public function setAge($age) {
        if (!is_int($age) || $age < 0 || $age > 99)
            throw new InvalidArgumentException("Parameter \"age\" is not valid. Only 2 digit numbers are allowed.");
        $this->age = $age;
        return $this;
    }

    /**
     * Sets value of coppa parameter
     * 
     * @param int $coppa
     * 
     * @return object Returns current object for fluent interface
     */

    public function setCoppa($coppa) {
        if (!is_int($coppa) || $coppa != 0 || $coppa != 1 || $coppa === true || $coppa === false)
            throw new InvalidArgumentException("Parameter \"coppa\" is not valid.");
        $this->coppa = $coppa;
        return $this;
    }

    /**
     * Sets value of city parameter
     * 
     * @param string $city
     * 
     * @return object Returns current object for fluent interface
     */

    public function setCity($city) {
        $this->city = $city;
        return $this;
    }

    /**
     * Sets value of gps parameter
     * 
     * @param string $gps
     * 
     * @return object Returns current object for fluent interface
     */

    public function setGps($gps) {
        $this->gps = $gps;
        return $this;
    }

    /**
     * Sets value of region parameter
     * 
     * @param string $region
     * 
     * @return object Returns current object for fluent interface
     */

    public function setRegion($region) {
        $this->region = $region;
        return $this;
    }

    /**
     * Sets value of zip parameter
     * 
     * @param int $zip
     * 
     * @return object Returns current object for fluent interface
     */

    public function setZip($zip) {
        $this->zip = $zip;
        return $this;
    }

    /**
     * Sets value of iosadid parameter
     * 
     * @param string $iosadid
     * 
     * @return object Returns current object for fluent interface
     */

    public function setIosAdId($iosadid) {
        $this->iosadid = $iosadid;
        return $this;
    }

    /**
     * Sets value of iosadtracking parameter
     * 
     * @param string $iosadtracking
     * 
     * @return object Returns current object for fluent interface
     */

    public function setIosAdTracking($iosadtracking) {
        $this->iosadtracking = $iosadtracking;
        return $this;
    }

    /**
     * Sets value of googleadid parameter
     * 
     * @param string $googleadid
     * 
     * @return object Returns current object for fluent interface
     */

    public function setGoogleAdId($googleadid) {
        $this->googleadid = $googleadid;
        return $this;
    }

    /**
     * Sets value of googlednt parameter
     * 
     * @param string $googlednt
     * 
     * @return object Returns current object for fluent interface
     */

    public function setGoogleDnt($googlednt) {
        $this->googlednt = $googlednt;
        return $this;
    }
    
    /**
     * Sets value of did parameter
     * 
     * @param string $did
     * 
     * @return object Returns current object for fluent interface
     */

    public function setAdvertisingId($did) {
        $this->did = $did;
        return $this;
    }

    /**
     * Sets value of diddnt parameter
     * 
     * @param string $diddnt
     * 
     * @return object Returns current object for fluent interface
     */

    public function setAdvertisingIdDoNotTrack($diddnt) {
        $this->diddnt = $diddnt;
        return $this;
    }
    
    /**
     * Sets value of wpid parameter
     * 
     * @param string $wpid
     * 
     * @return object Returns current object for fluent interface
     */

    public function setWpId($wpid) {
        $this->wpid = $wpid;
        return $this;
    }

    /**
     * Sets value of bbid parameter
     * 
     * @param string $bbid
     * 
     * @return object Returns current object for fluent interface
     */

    public function setBbId($bbid) {
        $this->bbid = $bbid;
        return $this;
    }

    /**
     * Sets value of ref parameter
     * 
     * @param string $ref
     * 
     * @return object Returns current object for fluent interface
     */

    public function setReferrerUrl($ref) {
        $this->ref = $ref;
        return $this;
    }

    /**
     * Sets value of htmlwithoutp parameter
     * 
     * @param boolean|int $htmlwithoutp
     * 
     * @return object Returns current object for fluent interface
     */

    public function setHtmlWithoutP($htmlwithoutp) {
        $this->htmlwithoutp = $htmlwithoutp;
        return $this;
    }

    /**
     * Sets value of timeout parameter
     * 
     * @param int $timeout
     * 
     * @return object Returns current object for fluent interface
     */

    public function setTimeout($timeout) {
        if(!is_int($timeout) || $timeout < 1000) {
            $this->timeout = 1000;
            trigger_error("Timeout should be integer and at least 1000 milliseconds.", E_USER_WARNING);
        }    
        else
            $this->timeout = $timeout;
        return $this;
    }

}