<?php
require_once "classes/book.php";
require_once "classes/movie.php";
require_once "vendor/autoload.php";
use Ahc\Jwt\JWT;

Class Client{
    private $dom;
    private $html;
    private $head;
    private $body;
    private $responseHolder;
    private $book;
    private $movie;
    private $jwt;
    private $serverUrl = "https://lamia.izba.ovh/server/";
    private $serverEndpoints;
    private $javascript;
    private $site;
    private $subsite;

    public function __construct($site = "movie", $subsite = "scrape", $javascript = false){
        $this->jwt = new JWT("TosiSalainenAvain");
        $this->book = new Book();
        $this->movie = new Movie();
        $this->site = $site;
        $this->subsite = $subsite;
        $this->javascript = (bool)$javascript;
	    $this->serverUrl = "https://lamia.izba.ovh/server/";
	    $this->serverEndpoints = array("book" => "getBook/", "movie" => "getMovie/");
    }

    public function constructSite(){

        if($this->javascript)
        {
            if(strcmp($this->site, "book") == 0)
                $this->bookJavascript($this->subsite);
            else
                $this->movieJavascript($this->subsite);
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

    private function getResultBook($data){
    	$url = $this->serverUrl . $this->serverEndpoints["book"];
        $response = json_decode($this->curlRequest($url, "POST", $data),true);
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

    private function getResultMovie($data){
	    $url = $this->serverUrl . $this->serverEndpoints["movie"];
        $response = json_decode($this->curlRequest($url, "POST", $data), true);
        $parentNode = $this->responseHolder ?? $this->dom;
        $error = $response["error"] ?? $response["Error"] ?? false;
        if(!$error)
        {
            if(isset($response["Search"]))
            {
                $list = $this->createElement($parentNode, "ul", array("class" => "moviePreviewList"));
                foreach($response["Search"] as $mr)
                {
                    $httpQueryTitle = http_build_query(array("site" => "movie", "subsite" => "scrape", "title" => $mr["Title"]));
                    $listItem = $this->createElement($list, "li", array("class" => "moviePreviewWrap"));
                    $listLink = $this->createElement($listItem, "a", array(
                        "href" => "/client/?$httpQueryTitle"
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

    public function getResult($fields){
            if(!isset($this->dom))
                $this->dom = new DOMDocument();
            $token = $this->jwt->encode($fields);
            $data = array("token" => $token);
            if(strcmp($this->site, "book") == 0)
                $this->getResultBook($data);
            else
                $this->getResultMovie($data);
    }

    function curlRequest($url, $type, $fields){
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
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        return curl_exec($curl);
    }

    function createElement($parentNode,$type,$attributes = array(),$innerHTML = null){
        $newElement = $this->dom->createElement($type);
        foreach($attributes as $key => $value)
        {
            $newElement->setAttribute($key, $value);
        }
        if(isset($innerHTML))
            $newElement->appendChild(new DOMText($innerHTML)); 
        $parentNode->appendChild($newElement);
        return $newElement;
    }

    private function createBase($bodyClass){
        $domImplementation = new DOMImplementation();
    
        $dtd = $domImplementation->createDocumentType("html");
    
        $this->dom = $domImplementation->createDocument("","",$dtd); 
        

        $this->html = $this->createElement($this->dom, "html");
        $this->head = $this->createElement($this->html,"head");
        $this->body = $this->createElement($this->html,"body", array("class" => $bodyClass."Body"));

        $this->createElement($this->head, "link", array("rel" => "stylesheet", "href" => "/client/styles/default.css"));
        $this->createElement($this->head, "script", array("src" => "/client/scripts/main.js"));

        $this->createElement($this->head, "meta", 
            array("charset" => "utf-8", 
            "content" => "width=device-width, initial-scale=1",
            "name" => "viewport" ));

        $this->createElement($this->head, "title", array(), "LamiaOy Practical Task");

        $header = $this->createElement($this->body, "header");
        $navi = $this->createElement($header, "nav");
        $this->createElement($this->body, "section", array("id" => "content", "class" => $bodyClass."Section"));

        $this->createElement($navi, "a", array("href" => "/client/?site=movie", "onclick" => "loadPage(\"movie/scrape\", event)"), "Movie-Scraper");
        $this->createElement($navi, "a", array("href" => "/client/?site=book", "onclick" => "loadPage(\"book/scrape\", event)"), "Book-Scraper");
    }

    private function footerBuilder(){
        
        $footer = $this->createElement($this->body, "footer");
        $ul = $this->createElement($footer,"ul");
        $github = $this->createElement($ul, "li", array());
        $this->createElement($github, "b", array(), "GitHub: ");
        $this->createElement($github, "a", array("href" => "https://github.com/izballs/LamiaWorkApply"), "github.com/izballs/LamiaWorkApply");
        $this->createElement($ul, "li", array(), "LamiaOy: WorkApply");
    }
    private function bookJavascript($sub){
            $this->dom = new DOMDocument();
            $this->bookBase($this->dom);
    }    
    private function movieJavascript($sub){
            $this->dom = new DOMDocument();
            $this->movieBase($this->dom);
    }
 
 
    private function movieBase($wrap){
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
        if(strcmp($this->subsite, "scrape") == 0)
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
            "href" => "/client/?site=movie&subsite=scrape",
            "onclick" => "loadPage(\"movie/scrape\", event)"
        ),"MovieScrape");
        $this->createElement($searchChooserSearch, "a",array(
            "href" => "/client/?site=movie&subsite=search",
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
                            "name" => "subsite",
                            "value" => $this->subsite,
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
                            "value" => ucfirst($this->subsite)
        ));

        $this->responseHolder = $this->createElement($wrapper, "div", array(
                            "id" => "responseHolder"));
 
    }

   private function bookBase($wrap){
        
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
        if(strcmp($this->subsite, "scrape") == 0)
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
            "href" => "/client/?site=book&subsite=scrape",
            "onclick" => "loadPage(\"book/scrape\", event)"
            ),"BookScrape");
        $this->createElement($searchChooserSearch, "a",array(
            "href" => "/client/?site=book&subsite=search",
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
                            "name" => "subsite",
                            "value" => $this->subsite,
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
