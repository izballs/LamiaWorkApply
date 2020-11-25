<?php
require_once "classes/scraped.php";
Class Book extends Scraped{

    private $dataHolder;

    public final function setData($newData){
        $this->dataHolder = $newData;
    }

    public function returnBookFull(){
        $dom = new DOMDocument();
        $bookWrap = $dom->createElement("div");
        $bookWrap->setAttribute("class", "bookWrap");
        if(isset($this->dataHolder["covers"]))
        {
            $temp = $dom->createElement("img");
            $temp->setAttribute("class", "bookCover");
            $temp->setAttribute("align", "right");
            $temp->setAttribute("src", "https://covers.openlibrary.org/b/id/".$this->dataHolder["covers"][0]."-M.jpg");
            $bookWrap->appendChild($temp);
        }
        foreach($this->dataHolder as $key => $value)
        {
            if(is_array($value))
            {
                $this->arrayHandler($value, $key, $bookWrap, "bookInfo", $dom);
            }
            else
            {
                $temp = $dom->createElement("p");
                $temp->appendChild(new DOMText("$key: $value"));
                $temp->setAttribute("class", "bookInfo");
                $bookWrap->appendChild($temp);
            }
        }
        return $bookWrap;
    }
}

?>
