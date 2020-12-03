<?php
declare(strict_types=1);

namespace lamia\client;

Class Scraped {
    protected $dataHolder;

  
    public function setData(array $newData){
        foreach($newData as $key => $value)
            $this->dataHolder{$key} = $value;
    }

    public function getFields(): array{
        return $this->dataHolder;
    }

    public function arrayHandler(array $array, string $lastKey, object $wrapper, string $className, object $dom){
        $ul = $dom->createElement("ul");
        $temp = $dom->createElement("label");
        if($lastKey != null && strcmp($lastKey, "0") != 0)
        {
            $temp->appendChild(new \DOMText((string)$lastKey));
            $wrapper->appendChild($temp);
        }
        $wrapper->appendChild($ul);
        foreach($array as $k => $v){
            if(is_array($v))
            {
                $temp = $dom->createElement("li");
                $temp->setAttribute("class", $className);
                $ul->appendChild($temp);
                $k = (string)$k;
                if($k != null && strcmp($k, "0") != 0)
                {
                    $temp->appendChild(new \DOMText((string)$k));
                }
                $this->arrayHandler($v, $k, $temp, $className, $dom);
            }
            else
            {
                $temp = $dom->createElement("li");
                $temp->appendChild(new \DOMText((string)$v));
                $temp->setAttribute("class", $className);
                $ul->appendChild($temp);
            }
        }
    }
}
?>
