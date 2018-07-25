<?php

    require_once 'vendor/autoload.php';
    require_once 'src/LeProgres.php';


    $page = (isset($_GET['page'])) ? $_GET['page'] : 1;
    if(!is_numeric($page) || $page < 1) {
        $page = 1;
    }

    $perPage = (isset($_GET['perPage'])) ? $_GET['perPage'] : 20;
    if(!is_numeric($page) || $page < 1 || $perPage > 50) {
        $perPage = 20;
    }

    $contentType = (isset($_GET['contentType'])) ? $_GET['contentType'] : "All";

    $leProgres = new LeProgres();
    try {
        $contents = $leProgres->getLastContents($page, $perPage, $contentType);

        header("Content-Type: application/json");
        echo json_encode($contents);
    } catch (Exception $e) {
        header("HTTP/1.0 400 Bad Request");
        die($e->getMessage());
    }