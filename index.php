<?php 
declare(strict_types=1);
require_once 'vendor/autoload.php';

use Lamia\Common\HttpClient;
use Lamia\Common\TokenHandler;
use Lamia\Client\Client;
use Lamia\Client\Book;
use Lamia\Client\Movie;
use Lamia\Server\BookController;
use Lamia\Server\MovieController;
use Lamia\Server\Server;
use Lamia\Common\Container;
use Ahc\Jwt\JWT;

$config = parse_ini_file("src/config/config.ini", true);

$container = new Container;
$container->setParameters(JWT::class, $config["JWT"]);

$config["TokenHandler"]["jwt"] = $container->get(JWT::class); 

$container->setParameters(TokenHandler::class, $config["TokenHandler"]);
$container->setParameters(Client::class, $config["Client"]);

$config["MovieController"]["httpClient"] = $container->get(HttpClient::class);
$config["MovieController"]["TokenHandler"] = $container->get(TokenHandler::class);
$config["BookController"]["httpClient"] = $container->get(HttpClient::class);
$config["BookController"]["TokenHandler"] = $container->get(TokenHandler::class);

$container->setParameters(MovieController::class, $config["MovieController"]);
$container->setParameters(BookController::class, $config["BookController"]);

$config["Server"]["controllers"] = array("book" => $container->get(BookController::class), 
    "movie" => $container->get(MovieController::class));

$container->setParameters(Server::class, $config["Server"]);

if(isset($_SERVER["REQUEST_URI"]))
{
    if(!stripos($_SERVER["REQUEST_URI"],"index.php"))
    {
        $request = substr($_SERVER["REQUEST_URI"], 1);
        $request = explode("/",$request);
        if(strcmp($request[0], "server") == 0 )
        {
            header('Content-Type: application/json');
            $server = $container->get(Server::class);
            $server->handleRequest($request[1], $_POST);
            return;
        }
        
    }
    $config["Client"]["site"]       = $_GET["site"] ?? $_POST["site"] ?? $config["Client"]["site"];
    $config["Client"]["subSite"]    = $_GET["subSite"] ?? $_POST["subSite"] ?? $config["Client"]["subSite"];
    $config["Client"]["javascript"] = !empty($_POST["javascript"]) ?? false;
    $config["Client"]["httpClient"] = $container->get(HttpClient::class);
    $config["Client"]["TokenHandler"] = $container->get(TokenHandler::class);
    $config["Client"]["book"] = $container->get(Book::class);
    $config["Client"]["movie"] = $container->get(Movie::class);
    $container->setParameters(Client::class, $config["Client"]);
    $client = $container->get(Client::class);
    $title = 
    $data = array(
        "title" => $_GET["title"] ?? $_POST["title"] ?? null,
        "search" => $_GET["search"] ?? $_POST["search"] ?? null,
        "isbn" => $_GET["isbn"] ?? $_POST["isbn"] ?? null,
        "year" => $_GET["year"] ?? $_POST["year"] ?? null,
        "plot" => $_GET["plot"] ?? $_POST["plot"] ?? $config["Client"]["plot"]
    );

    if($config["Client"]["javascript"])
        if($data["title"] !== null || $data["search"] !== null || $data["isbn"] !== null)
            $client->getResult($data);
        else
            $client->constructSite();
    else
    {
        $client->constructSite();
        if($data["title"] !== null || $data["search"] !== null || $data["isbn"] !== null)
            $client->getResult($data);
    }   
    $client->echoPage();
    return;
}
?>
