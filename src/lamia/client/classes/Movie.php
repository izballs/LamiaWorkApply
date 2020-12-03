<?php
declare(strict_types=1);

namespace lamia\client;

Class Movie extends Scraped{
 
    public function returnMovieFull($className){
        $dom = new \DOMDocument();
        $movieWrap = $dom->createElement("div");
        $movieWrap->setAttribute("class", $className); 
        $infoWrap = $dom->createElement("div");

        if(isset($this->dataHolder["Poster"]))
        {
            $temp = $dom->createElement("img");
            $temp->setAttribute("class", "moviePoster");
            $temp->setAttribute("align", "left");
            $temp->setAttribute("src", $this->dataHolder["Poster"]);
            $movieWrap->appendChild($temp);
        }
        foreach($this->dataHolder as $key => $value)
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
                $temp->appendChild(new \DOMText("$key: $value"));
                $temp->setAttribute("class","movieInfo");
                $infoWrap->appendChild($temp);
            }
        }
        $movieWrap->appendChild($infoWrap);
        return $movieWrap;
    }
}


?>
