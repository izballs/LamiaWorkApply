<?php
require_once "../classes/basescraper.php";
Class BookScraper extends BaseScraper{

    private $worksUrl = "https://openlibrary.org/works/";
    private $searchUrl = "https://openlibrary.org/search.json?";
    function __construct(){ 
        $this->url = "https://openlibrary.org/isbn/";
    }

    public function getBookInfo($isbn){
       $url = $this->url . $isbn . ".json";
       $json_data = $this->curlRequest($url, "GET", null); 
       $data = json_decode($json_data, true);
       $respond = $json_data;
       if(isset($data["works"]))
       {
            $exploded = explode("/", $data["works"][0]["key"]);
            $url = $this->worksUrl . $exploded[2] . ".json";
            $more_json_data = $this->curlRequest($url, "GET", null);
            $more_data = json_decode($more_json_data, true);
            $respond = json_encode(array_merge_recursive($data, $more_data));
       }
       return $respond;

    }
    public function searchBook($search){
        $url = $this->searchUrl . http_build_query(array("title" => $search));
        return $this->curlRequest($url, "GET", null); 

    }
}
