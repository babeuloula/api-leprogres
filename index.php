<?php

    use LeProgres\Api\Api;
    use Slim\App;
    use Slim\Container;
    use Slim\Exception\MethodNotAllowedException;
    use Slim\Exception\NotFoundException;
    use Slim\Http\Request;
    use Slim\Http\Response;

    require 'vendor/autoload.php';

    $config = require('config.php');

    $app = new App($config);


    $app->get('/lastContents', function (Request $request, Response $response, array $args) : Response {
        $get = $request->getParams();

        $page = (isset($get['page'])) ? $get['page'] : 1;
        if(!is_numeric($page) || $page < 1) {
            $page = 1;
        }

        $perPage = (isset($get['perPage'])) ? $get['perPage'] : 20;
        if(!is_numeric($page) || $page < 1 || $perPage > 50) {
            $perPage = 20;
        }

        $contentType = (isset($get['contentType'])) ? $get['contentType'] : "All";


        $api = new Api();
        try {
            $lastContents = $api->getLastContents($page, $perPage, $contentType);
            return $response->withJson($lastContents);
        } catch (Exception $e) {
            return $response->withJson($e->getMessage(), ($e->getCode() !== 0) ? $e->getCode() : 500);
        }
    });


    $app->get('/oneContent', function(Request $request, Response $response, array $args) : Response {
        $get = $request->getParams();

        $cmsUrl = (isset($get['cmsUrl'])) ? $get['cmsUrl'] : null;

        if(is_null($cmsUrl)) {
            return $response->withJson("Le paramètres cmsUrl est obligatoire.", 400);
        }


        $api = new Api();
        try {
            $oneContent = $api->getOneContent($cmsUrl);
            return $response->withJson($oneContent);
        } catch (Exception $e) {
            return $response->withJson($e->getMessage(), ($e->getCode() !== 0) ? $e->getCode() : 500);
        }
    });


    $container = $app->getContainer();


    // Gestion des erreurs Slim et PHP
    $container['errorHandler'] = function (Container $c) : callable {
        return function (Request $request, Response $response, Exception $exception) use ($c) : Response {
            return $response->withJson($exception->getMessage(), 500);
        };
    };
    $container['phpErrorHandler'] = function (Container $c) : callable  {
        return function (Request $request, Response $response, Error $error) use ($c) : Response {
            return $response->withJson($error->getMessage(), 500);
        };
    };

    // Gestion des 404
    $container['notFoundHandler'] = function (Container $c) : callable  {
        return function (Request $request, Response $response) use ($c) : Response {
            return $response->withJson("Not found", 404);
        };
    };
    // Gestion des 405
    $container['notAllowedHandler'] = function (Container $c) : callable  {
        return function (Request $request, Response $response, array $allowMethods) use ($c) : Response {
            return $response->withJson("Method not allowed. Allowed methods:" . join(", ", $allowMethods), 405);
        };
    };


    $app->run();