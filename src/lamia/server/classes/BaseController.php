<?php
declare(strict_types=1);
namespace Lamia\Server;
interface BaseController
{
    public function handlePayload(array $payload);
    public function printResponse():string;
}

?>
