<?php
declare(strict_types=1);
namespace Lamia\Common;
use Ahc\Jwt\JWT;
class TokenHandler{
    private $jwt;
    private $tokenEnabled;

    function __construct(JWT $jwt, bool $tokenEnabled){
        $this->jwt = $jwt;
        $this->tokenEnabled = $tokenEnabled;
    }
    public function decodeToken(array $tokenData) : ?array{
        if($this->tokenEnabled)
        {
            if(!isset($tokenData["token"]))
                return null;
            else
                return $this->jwt->decode($tokenData["token"]);
        }
        else
            return $tokenData;
    }
    public function encodeToken(array $tokenData) : ?array{
        if($this->tokenEnabled)
            return array("token" => $this->jwt->encode($tokenData));
        else
            return $tokenData;
    }
}

?>
