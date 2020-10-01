<?php

namespace LeProgres\Api;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Request
{

    /**
     * Permet d'exécuter les requêtes de l'API officielle du Progrès
     *
     * @param String $url URL de l'API
     * @param String $type Type de la requête
     *
     * @return mixed[] Données parsées correctement
     * @throws Exception
     */
    public static function execute(string $url, string $type): array
    {
        $client = new Client();

        try {
            $res = $client->request('GET', $url);
        } catch (GuzzleException | Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }


        $body = json_decode($res->getBody()->getContents());


        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Erreur lors de la conversion des contenus de l'API", 500);
        }


        if ($res->getStatusCode() === 404 || $body->returnCode === 404) {
            throw new Exception("Impossible de trouver le contenu dans l'API : " . $body->message, 404);
        } elseif ((int) $res->getStatusCode() !== 200 || (false === $body->success && $body->returnCode !== 0)) {
            // Oui ne cherchez pas pourquoi, le code de retour de l'API n'est pas 200 mais 0 si la requête est bonne
            throw new Exception(
                sprintf(
                    "Erreur lors de la récupération des contenus dans l'API : %s",
                    $body->message,
                ),
                $res->getStatusCode()
            );
        }


        $parser = new Parser();
        $body = $parser->parse($body, $type);

        return $body;
    }
}
