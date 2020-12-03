<?php 
namespace Lamia\Server;
Class Server{
    private $endPoints;
    function __construct(array $endPoints, array $controllers){
        foreach($endPoints as $key => $value)
            $this->endPoints[$value] = $controllers[$key];
    }
    public function handleRequest(string $requestedEndpoint, array $fields){
        if(isset($this->endPoints[$requestedEndpoint]))
        {
            $this->endPoints[$requestedEndpoint]->handlePayload($fields);
            echo $this->endPoints[$requestedEndpoint]->printResponse();
        }
        else
            echo json_encode(array("Error" => "Endpoint not found. Try Again.", "EndPoint" => $requestedEndpoint));
    }
}
?>
