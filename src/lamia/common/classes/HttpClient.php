<?php
declare(strict_types=1);

namespace Lamia\Common;
Class HttpClient{
    private $curl;

    function __construct(){
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    }
    function getRequest(string $url, ?array $fields) : string
    {
        if($fields !== null)
        {    
            $url .=http_build_query($fields);
        }
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($this->curl, CURLOPT_URL, $url);
        return curl_exec($this->curl);
    }
    function postRequest(string $url, array $fields) : string
    {
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($this->curl, CURLOPT_URL, $url);
        return curl_exec($this->curl);
    }
}

?>
