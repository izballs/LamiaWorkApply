<?php

Class Scraped {
    
    public function setData($newData){
        foreach($newData as $key => $value)
            $this->{$key} = $value;
    }

    public function getFields(){
        $fields = array();
        foreach($this as $key => $value)
            array_push($fields, $key);
        return $fields;
    }

    public function arrayHandler($array, $lastKey, $wrapper, $className, $dom){
        $ul = $dom->createElement("ul");
        $temp = $dom->createElement("label");
        if($lastKey != null && strcmp($lastKey, "0") != 0)
        {
        $temp->appendChild(new DOMText($lastKey));
        $wrapper->appendChild($temp);
        }
        $wrapper->appendChild($ul);
        foreach($array as $k => $v){
            if(is_array($v))
            {
                $temp = $dom->createElement("li");
                $temp->setAttribute("class", $className);
                $ul->appendChild($temp);
                if($k != null && strcmp($k, "0") != 0)
                {
                    $temp->appendChild(new DOMText($k));
                }
                $this->arrayHandler($v, $k, $temp, $className, $dom);
            }
            else
            {
                $temp = $dom->createElement("li");
                $temp->appendChild(new DOMText($v));
                $temp->setAttribute("class", $className);
                $ul->appendChild($temp);


            }
        }
    }



}
?>
