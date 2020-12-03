<?php 
declare(strict_types=1);
namespace Lamia\Server;
use Lamia\Common\HttpClient;
use Lamia\Common\TokenHandler;
Class MovieController implements BaseController{

    private $httpClient;
    private $tokenHandler;
    private $url;
    private $apikey;
    private $response;


    function __construct(HttpClient $httpClient, TokenHandler $tokenHandler, string $apiKey, string $url){
        $this->httpClient   = $httpClient;
        $this->tokenHandler = $tokenHandler;
        $this->apikey       = $apiKey;
        $this->url          = $url;
    }
    public function handlePayload($payload){
        $payload = $this->tokenHandler->decodeToken($payload);
        if($payload !== null)
        {
            if(!empty($payload["title"]))
                $this->response = $this->getMovieInfo($payload["title"], $payload["year"], $payload["plot"] ?? "short");
            else if(!empty($payload["search"]))
                $this->response = $this->searchMovie($payload["search"]);
            else
                $this->response = json_encode(array("Error" => "No isbn or search parameter found. Please try again."));
        }
        else
            $this->response = json_encode(array("Error" => "Token is not set. Please use official client."));
    }

    public function printResponse():string{
        return $this->response;
    }


    private function getMovieInfo(string $title, ?string $year="", ?string $plot="short"){
        $fields = array(
            "apikey" => $this->apikey,
            "t" => $title,
            "y" => $year ,
            "plot" => $plot
    );
        $response = $this->httpClient->getRequest($this->url, $fields);
        return $response;
    }

    public function searchMovie($title){
        $fields = array(
            "apikey" => $this->apikey,
            "s" => $title
        );
        $response = $this->httpClient->getRequest($this->url, $fields);
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
