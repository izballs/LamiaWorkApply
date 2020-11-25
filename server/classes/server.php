<?php
require_once "../vendor/autoload.php";
require_once "../classes/bookscraper.php";
require_once "../classes/moviescraper.php";
use Ahc\Jwt\JWT;
Class Server{
    private $bs;
    private $ms;
    private $jwt;
    private $response;
    private $tokenEnabled;

    function __construct()
    {
        $this->bs = new BookScraper();
        $this->ms = new MovieScraper();
        $this->jwt = new JWT("TosiSalainenAvain");
        $this->response = null;
	    $this->tokenEnabled = true;
    }

    private function parseTokenData($data){
        if(!isset($data["token"]))
        {
            $this->response = json_encode(array("Error" => "Token is not set. Please use official client"));
	        return;
        }
        return $this->jwt->decode($data["token"]);
    }

    public function handleTransactionMovie($payload){
	if($this->tokenEnabled)
        	$payload = $this->parseTokenData($payload);
        if(isset($payload["title"]))
            $this->response = $this->ms->getMovieInfo($payload["title"], $payload["year"], $payload["plot"]);
        else if(isset($payload["search"]))
            $this->response = $this->ms->searchMovie($payload["search"]);
        else if($this->response == null)
            $this->response = array("Error" => "No title or search parameter found. Please try again.");            
    }

    public function handleTransactionBook($payload){
	if($this->tokenEnabled)
		$payload = $this->parseTokenData($payload);
        if(isset($payload["isbn"]))
            $this->response = $this->bs->getBookInfo($payload["isbn"]);
        else if(isset($payload["search"]))
            $this->response = $this->bs->searchBook($payload["search"]);
        else if($this->response == null)
            $this->response = array("Error" => "No isbn or search parameter found. Please try again.");            
    }
    
    public function printResponse(){
    	echo $this->response;
    }
}
?>

