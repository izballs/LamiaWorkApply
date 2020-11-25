<?php 
Class BaseScraper{
    protected $url;
    protected $apikey;

    protected function curlRequest($url, $type, $fields){
        $curl = curl_init();
        if(is_array($fields) && $type == "GET")
        {    
            $url .=http_build_query($fields);
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
        if($type == "POST"){
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($fields));
            curl_setopt($curl, CURLOPT_POST, 1);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        return curl_exec($curl);
}

}
?>
