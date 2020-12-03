<?php
declare(strict_types=1);

namespace Lamia\Client;
use Lamia\Client\Scraped;

Class Book extends Scraped{

    public function returnBookFull():object{
        $dom = new \DOMDocument();
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
                $temp->appendChild(new \DOMText("$key: $value"));
                $temp->setAttribute("class", "bookInfo");
                $bookWrap->appendChild($temp);
            }
        }
        return $bookWrap;
    }
}

?>
