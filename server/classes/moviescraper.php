<?php 
require_once "../classes/basescraper.php";

Class MovieScraper extends BaseScraper{

    function __construct(){
        $this->apikey = "b0df86b6";
        $this->url = "http://www.omdbapi.com/?";
    }

    public function getMovieInfo($title, $year=null, $plot="short"){
        $fields = array(
            "apikey" => $this->apikey,
            "t" => $title,
            "y" => $year,
            "plot" => $plot
    );
        $response = $this->curlRequest($this->url, "GET", $fields);
        return $response;
    }
    public function searchMovie($title){
        $fields = array(
            "apikey" => $this->apikey,
            "s" => $title
        );
        $response = $this->curlRequest($this->url, "GET", $fields);
        return $response;
    }



    public function getMovieList($searchTag) {
        
        $fields = array(
            "apikey" => $this->apikey,
            "s" => $searchTag
        );
        $response = curlRequest($this->url, "GET", $fields);
        return $response;

    }

    
}

?>
