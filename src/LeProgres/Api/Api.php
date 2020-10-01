<?php

namespace LeProgres\Api;

use Exception;

class Api
{

    /** @var string */
    private $base = "http://www.leprogres.fr/app_mobile/";

    /** @var string */
    private $lastContents = "listcontentbytype?";
    /** @var string */
    private $oneContent = "detailwebcontent?";

    /** @var string[] */
    private $appVersion = ["appversion" => "2.9.1"];
    /** @var string[] */
    private $osVersion = ["osversion" => "7.0.1"];
    /** @var string[] */
    private $appToken = ["pTokenApplication" => "8C841AA5-33C3-4325-80B1-C3A931579AF3"];
    /** @var int[] */
    private $terminalMode = ["pTerminalMode" => 3];
    /** @var false[] */
    private $userAuthenticated = ["isuserAuthenticated" => false];

    /** @var string[] */
    private $aviableContentType = [
        "All",
        "RichContent",
        "Gallery",
        "Video",
        "Audio",
        "Live",
    ];

    /** @var string */
    private $url;


    /**
     * Récupère les derniers articles
     *
     * @param int $page
     * @param int $per_page
     * @param string $contentType
     *
     * @return mixed[]
     * @throws Exception
     */
    public function getLastContents(int $page = 1, int $per_page = 20, string $contentType = "All"): array
    {
        if (false === \in_array($contentType, $this->aviableContentType)) {
            throw new Exception(sprintf("ContentType %s inconnu", $contentType), 400);
        }

        $params = array_merge(
            [
                "page" => $page,
                "per_page" => $per_page,
                "contenttype" => $contentType,
            ],
            $this->appVersion,
            $this->osVersion,
            $this->appToken,
            $this->terminalMode,
            $this->userAuthenticated
        );

        $params = http_build_query($params);

        $this->url = $this->base . $this->lastContents . $params;

        return Request::execute($this->url, "lastContents");
    }


    /**
     * Récupère l'article en fonction de son URL
     *
     * @param string $cmsUrl
     *
     * @return mixed[]
     * @throws Exception
     */
    public function getOneContent(string $cmsUrl): array
    {
        $params = array_merge(
            [
                "cmsurl" => $cmsUrl,
            ],
            $this->appToken,
            $this->terminalMode,
            $this->userAuthenticated
        );

        $params = http_build_query($params);

        $this->url = $this->base . $this->oneContent . $params;

        return Request::execute($this->url, "oneContent");
    }


    /**
     * Permet de récupéré l'URL de l'API officielle qui est appelé
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }
}
