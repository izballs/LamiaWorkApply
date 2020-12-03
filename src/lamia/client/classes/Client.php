<?php
declare(strict_types=1);

namespace Lamia\Client;

use Lamia\Client\Book;
use Lamia\Client\Movie;
use Lamia\Common\HttpClient;
use Lamia\Common\TokenHandler;

Class Client{
    private $dom;
    private $html;
    private $head;
    private $body;
    private $responseHolder;
    private $book;
    private $movie;
    private $httpClient;
    private $tokenHandler;
    private $serverUrl;
    private $serverEndpoints;
    private $javascript;
    private $site;
    private $subSite;

    public function __construct(HttpClient $httpClient, TokenHandler $tokenHandler, 
        string $serverUrl, array $serverEndpoints, Book $book, 
        Movie $movie, string $site, string $subSite,bool $javascript){
        $this->httpClient       = $httpClient;
        $this->tokenHandler     = $tokenHandler;
	    $this->serverUrl        = $serverUrl;
	    $this->serverEndpoints  = $serverEndpoints;
        $this->book             = $book;
        $this->movie            = $movie;
        $this->site             = $site;
        $this->subSite          = $subSite;
        $this->javascript       = $javascript;
    }

    public function constructSite(){

        if($this->javascript)
        {
            if(strcmp($this->site, "book") == 0)
                $this->bookJavascript();
            else
                $this->movieJavascript();
        }
        else{
        $this->createBase($this->site);
        if(strcmp($this->site, "book") == 0)
        {
            $this->bookBase($this->dom->getElementsByTagName("section")[0]);
        }
        else
            $this->movieBase($this->dom->getElementsByTagName("section")[0]);
        $this->footerBuilder();
        }
    }
    public function echoPage(){
        echo $this->dom->saveHTML();
    }

    private function getResultBook(array $data){
    	$url = $this->serverUrl . $this->serverEndpoints["book"];
        $response = json_decode($this->httpClient->postRequest($url, $data),true);
        $parentNode = $this->responseHolder ?? $this->dom;
        $error = $response["error"] ?? $response["Error"] ?? false;
        if(!$error)
        {
            $this->book->setData($response);
            $book = $this->book->returnBookFull();
            $parentNode->appendChild($this->dom->importNode($book, true));
        }
        else
            $this->createElement($parentNode,"h2", array(), $error);
    }

    private function getResultMovie(array $data){
	    $url = $this->serverUrl . $this->serverEndpoints["movie"];
        $response = json_decode($this->httpClient->postRequest($url, $data), true);
        $parentNode = $this->responseHolder ?? $this->dom;
        $error = $response["error"] ?? $response["Error"] ?? false;
        if(!$error)
        {
            if(isset($response["Search"]))
            {
                $list = $this->createElement($parentNode, "ul", array("class" => "moviePreviewList"));
                foreach($response["Search"] as $mr)
                {
                    $httpQueryTitle = http_build_query(array("site" => "movie", "subSite" => "scrape", "title" => $mr["Title"]));
                    $listItem = $this->createElement($list, "li", array("class" => "moviePreviewWrap"));
                    $listLink = $this->createElement($listItem, "a", array(
                        "href" => "?$httpQueryTitle"
                    ));
                    $this->movie->setData($mr);
                    $movie = $this->movie->returnMovieFull("moviePreview");
                    $listLink->appendChild($this->dom->importNode($movie,true));
                }
                $parentNode->appendChild($list);
            }
            else{
                $this->movie->setData($response);
                $movie = $this->movie->returnMovieFull("movieWrap");
                $parentNode->appendChild($this->dom->importNode($movie, true));
            }
        }
        else
            $this->createElement($parentNode,"h2", array(), $error);
    }

    public function getResult(array $fields){
            if(!isset($this->dom))
                $this->dom = new \DOMDocument();
            $token = $this->tokenHandler->encodeToken($fields);
            if(strcmp($this->site, "book") == 0)
                $this->getResultBook($token);
            else
                $this->getResultMovie($token);
    }

    function createElement(object $parentNode, string $type,?array $attributes = array(),?string $innerHTML = null):object{
        $newElement = $this->dom->createElement($type);
        foreach($attributes as $key => $value)
        {
            $newElement->setAttribute($key, $value);
        }
        if(isset($innerHTML))
            $newElement->appendChild(new \DOMText($innerHTML)); 
        $parentNode->appendChild($newElement);
        return $newElement;
    }

    private function createBase(string $bodyClass){
        $domImplementation = new \DOMImplementation();
    
        $dtd = $domImplementation->createDocumentType("html");
    
        $this->dom = $domImplementation->createDocument("","",$dtd); 
        

        $this->html = $this->createElement($this->dom, "html");
        $this->head = $this->createElement($this->html,"head");
        $this->body = $this->createElement($this->html,"body", array("class" => $bodyClass."Body"));

        $this->createElement($this->head, "link", array("rel" => "stylesheet", "href" => "/src/lamia/client/styles/default.css"));
        $this->createElement($this->head, "script", array("src" => "/src/lamia/client/scripts/main.js"));

        $this->createElement($this->head, "meta", 
            array("charset" => "utf-8", 
            "content" => "width=device-width, initial-scale=1",
            "name" => "viewport" ));

        $this->createElement($this->head, "title", array(), "LamiaOy Practical Task");

        $header = $this->createElement($this->body, "header");
        $navi = $this->createElement($header, "nav");
        $this->createElement($this->body, "section", array("id" => "content", "class" => $bodyClass."Section"));

        $this->createElement($navi, "a", array("href" => "?site=movie", "onclick" => "loadPage(\"movie/scrape\", event)"), "Movie-Scraper");
        $this->createElement($navi, "a", array("href" => "?site=book", "onclick" => "loadPage(\"book/scrape\", event)"), "Book-Scraper");
    }

    private function footerBuilder(){
        
        $footer = $this->createElement($this->body, "footer");
        $ul = $this->createElement($footer,"ul");
        $github = $this->createElement($ul, "li", array());
        $this->createElement($github, "b", array(), "GitHub: ");
        $this->createElement($github, "a", array("href" => "https://github.com/izballs/LamiaWorkApply"), "github.com/izballs/LamiaWorkApply");
        $this->createElement($ul, "li", array(), "LamiaOy: WorkApply");
    }
    private function bookJavascript(){
            $this->dom = new \DOMDocument();
            $this->bookBase($this->dom);
    }    
    private function movieJavascript(){
            $this->dom = new \DOMDocument();
            $this->movieBase($this->dom);
    }
 
 
    private function movieBase(object $wrap){
        $wrapper = $this->createElement($wrap, "div", array( ));
        $this->createElement($wrapper, "h1", array(), "Movie-Scraper");
        $searchWrapper = $this->createElement($wrapper, "form",array(
                                                "action" => "#",
                                                "method" => "POST",
                                                "class" => "searchWrapper"
        ));
        $searchChooser = $this->createElement($searchWrapper,"ul",array("class" => "searchChooser"));
        $scScrapeClass = "searchChooserItem";
        $scSearchClass = "searchChooserItem";
        $titleName = "title";
        $titleClass = "scrapeTitle";
        $enableMoreOptions = true;
        if(strcmp($this->subSite, "scrape") == 0)
            $scScrapeClass .= " selected";
        else
        {
            $scSearchClass .= " selected";
            $titleName = "search";
            $titleClass = "searchTitle";
            $enableMoreOptions = false;
        }
        $searchChooserScrape = $this->createElement($searchChooser,"li", array("class" => $scScrapeClass));
        $searchChooserSearch = $this->createElement($searchChooser,"li", array("class" => $scSearchClass));
        $this->createElement($searchChooserScrape, "a",array(
            "href" => "?site=movie&subSite=scrape",
            "onclick" => "loadPage(\"movie/scrape\", event)"
        ),"MovieScrape");
        $this->createElement($searchChooserSearch, "a",array(
            "href" => "?site=movie&subSite=search",
            "onclick" => "loadPage(\"movie/search\", event)"
        ),"MovieSearch");
        $this->createElement($searchWrapper, "input", array(
                            "type" => "radio",
                            "checked" => "true",
                            "name" => "site",
                            "value" => "movie",
                            "hidden" => "true"
        ));
        $this->createElement($searchWrapper, "input", array(
                            "type" => "radio",
                            "checked" => "true",
                            "name" => "subSite",
                            "value" => $this->subSite,
                            "hidden" => "true"
        ));
        
        $this->createElement($searchWrapper, "input", array(
                            "type" => "text",
                            "name" => $titleName,
                            "required" => "true",
                            "placeHolder" => "Title",
                            "class" => $titleClass
        ));

        if($enableMoreOptions){
            $this->createElement($searchWrapper, "input", array(
                                "type" => "text",
                                "name" => "year",
                                "placeHolder" => "Year",
                                "class" => "scrapeYear"
            ));
            $this->createElement($searchWrapper, "label", array(), "Plot length: ");
            $this->createElement($searchWrapper, "input", array(
                                "type" => "radio",
                                "name" => "plot",
                                "checked" => "true",
                                "value" => "short"
            ));
            $this->createElement($searchWrapper, "label", array(), "Short");
            $this->createElement($searchWrapper, "input", array(
                                "type" => "radio",
                                "name" => "plot",
                                "value" => "full"
            ));
            $this->createElement($searchWrapper, "label", array(), "Full");
        }
        $this->createElement($searchWrapper, "input", array(
                            "type" => "submit",
                            "onclick" => "submitForm(event)",
                            "value" => ucfirst($this->subSite)
        ));

        $this->responseHolder = $this->createElement($wrapper, "div", array(
                            "id" => "responseHolder"));
 
    }

   private function bookBase(object $wrap){
        
        $wrapper = $this->createElement($wrap, "div", array( ));
        $this->createElement($wrapper, "h1", array(), "Book-Scraper");
        $searchWrapper = $this->createElement($wrapper, "form",array(
                                                "action" => "#",
                                                "method" => "POST",
                                                "class" => "searchWrapper"
        ));
        $searchChooser = $this->createElement($searchWrapper,"ul",array("class" => "searchChooser"));
        $scScrapeClass = "searchChooserItem";
        $scSearchClass = "searchChooserItem";
        $titleName = "isbn";
        $titlePlaceholder = "Book ISBN";
        $titleClass = "scrapeISBN";
        if(strcmp($this->subSite, "scrape") == 0)
            $scScrapeClass .= " selected";
        else
        {
            $scSearchClass .= " selected";
            $titleName = "search";
            $titleClass = "searchISBN";
            $titlePlaceholder = "Book title";
        }
 
        $searchChooserScrape = $this->createElement($searchChooser,"li", array("class" => $scScrapeClass));
        $searchChooserSearch = $this->createElement($searchChooser,"li", array("class" => $scSearchClass));
        $this->createElement($searchChooserScrape, "a",array(
            "href" => "?site=book&subSite=scrape",
            "onclick" => "loadPage(\"book/scrape\", event)"
            ),"BookScrape");
        $this->createElement($searchChooserSearch, "a",array(
            "href" => "?site=book&subSite=search",
            "onclick" => "loadPage(\"book/search\", event)"
            ),"BookSearch");
        $this->createElement($searchWrapper, "input", array(
                            "type" => "radio",
                            "checked" => "true",
                            "name" => "site",
                            "value" => $this->site,
                            "hidden" => "true"
        ));
         $this->createElement($searchWrapper, "input", array(
                            "type" => "radio",
                            "checked" => "true",
                            "name" => "subSite",
                            "value" => $this->subSite,
                            "hidden" => "true"
        ));
        
        $this->createElement($searchWrapper, "input", array(
                            "type" => "text",
                            "name" => $titleName,
                            "required" => "true",
                            "placeHolder" => $titlePlaceholder,
                            "class" => $titleClass
        ));
        $this->createElement($searchWrapper, "input", array(
                            "type" => "submit",
                            "onclick" => "submitForm(event)",
                            "value" => "Scrape",
        ));

        $this->responseHolder = $this->createElement($wrapper, "div", array(
                            "id" => "responseHolder"));
    }
    
}

?>
