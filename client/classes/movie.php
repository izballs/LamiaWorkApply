<?php
require_once "classes/scraped.php";
Class Movie extends Scraped{
    protected $Title;
    protected $Year;
    protected $Rated;
    protected $Released;
    protected $Runtime;
    protected $Genre;
    protected $Director;
    protected $Writer;
    protected $Actors;
    protected $Plot;
    protected $Language;
    protected $Country;
    protected $Awards;
    protected $Poster;
    protected $Ratings;
    protected $Metascore;
    protected $imdbRating;
    protected $imdbVotes;
    protected $imdbID;
    protected $Type;
    protected $DVD;
    protected $BoxOffice;
    protected $Production;
    protected $Website;
    protected $Response;

    public function returnMovieFull($className){
        $dom = new DOMDocument();
        $movieWrap = $dom->createElement("div");
        $movieWrap->setAttribute("class", $className); 
        $infoWrap = $dom->createElement("div");

        if(isset($this->Poster))
        {
            $temp = $dom->createElement("img");
            $temp->setAttribute("class", "moviePoster");
            $temp->setAttribute("align", "left");
            $temp->setAttribute("src", $this->Poster);
            $movieWrap->appendChild($temp);
        }
        foreach($this as $key => $value)
        {
            if(is_array($value))
            {
                $this->arrayHandler($value, $key, $infoWrap, "movieInfo", $dom);
            } 
            else if(strcmp($key, "Response") == 0 || strcmp($key, "Poster") == 0)
            {
            }
            else if (isset($value))
            {
                $temp = $dom->createElement("p");
                $temp->appendChild(new DOMText("$key: $value"));
                $temp->setAttribute("class","movieInfo");
                $infoWrap->appendChild($temp);
            }
            
        }
        $movieWrap->appendChild($infoWrap);
        return $movieWrap;
    }
}


?>
