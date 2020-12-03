<?php
declare(strict_types=1);
namespace Lamia\Server;
use Lamia\Common\HttpClient;
use Lamia\Common\TokenHandler;

Class BookController implements BaseController{

    private $httpClient;
    private $tokenHandler;
    private $worksUrl;
    private $searchUrl;
    private $response;

    function __construct(HttpClient $httpClient, TokenHandler $tokenHandler,string $worksUrl, string $isbnUrl,string $searchUrl){ 
        $this->httpClient   = $httpClient;
        $this->tokenHandler = $tokenHandler;
        $this->worksUrl     = $worksUrl;
        $this->isbnUrl      = $isbnUrl;
        $this->searchUrl    = $searchUrl;
    }

    public function handlePayload($payload){
        $payload = $this->tokenHandler->decodeToken($payload);
        if($payload !== null)
        {
            if(!empty($payload["isbn"]))
                $this->response = $this->getBookInfo($payload["isbn"]);
            else if(!empty($payload["search"]))
                $this->response = $this->searchBook($payload["search"]);
            else
                $this->response = json_encode(array("Error" => "No isbn or search parameter found. Please try again."));
        }
        else
            $this->response = json_encode(array("Error" => "Token is not set. Please use official client."));
    }

    public function printResponse():string{
        return $this->response;
    }

    private function getBookInfo(string $isbn): string{
       $url = $this->isbnUrl . $isbn . ".json";
       $json_data = $this->httpClient->getRequest($url, null); 
       $data = json_decode($json_data, true);
       $response = $json_data;
       if(isset($data["works"]))
       {
            $exploded = explode("/", $data["works"][0]["key"]);
            if($exploded[2] !== null)
            {
                $url = $this->worksUrl . $exploded[2] . ".json";
                $moreJsonData = $this->httpClient->getRequest($url, null);
                $moreData = json_decode($moreJsonData, true);
                $response = array_merge_recursive($data, $moreData);
            }
       }
       return json_encode($response);

    }
    private function searchBook($search): string{
        $url = $this->searchUrl . http_build_query(array("title" => $search));
        return $this->httpClient->getRequest($url, null); 
    }
}
