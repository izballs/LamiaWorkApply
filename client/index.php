<?php session_start();
require_once "classes/client.php";

$site = $_GET["site"] ?? $_POST["site"] ?? "movie";
$subsite = $_GET["subsite"] ?? $_POST["subsite"] ?? "scrape";
$javascript = isset($_POST["javascript"]);
$client = new Client($site, $subsite, $javascript);
$title = $_GET["title"] ?? $_POST["title"] ?? null;
$search = $_GET["search"] ?? $_POST["search"] ?? null;
$isbn = $_GET["isbn"] ?? $_POST["isbn"] ?? null;
$year = $_GET["year"] ?? $_POST["year"] ?? null;
$plot = $_GET["plot"] ?? $_POST["plot"] ?? "short";

$data = array(
    "title" => $title,
    "search" => $search,
    "isbn" => $isbn,
    "year" => $year,
    "plot" => $plot
);

if($javascript)
    if(isset($_POST["title"]) || isset($_POST["search"]) || isset($_POST["isbn"]))
        $client->getResult($data);
    else
    {
        $client->constructSite();
    }
else
{
    $client->constructSite();
    if(isset($title) || isset($search) || isset($isbn))
    {
        $client->getResult($data);
    }
}
$client->echoPage();

?>
