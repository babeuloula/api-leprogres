<?php

    namespace LeProgres\Api;

    use Exception;

    class Api {

        private $base = "http://www.leprogres.fr/app_mobile/";

        private $lastContents = "listcontentbytype?";
        private $oneContent   = "detailwebcontent?";

        private $appVersion        = ["appversion"          => "2.9.1"];
        private $osVersion         = ["osversion"           => "7.0.1"];
        private $appToken          = ["pTokenApplication"   => "8C841AA5-33C3-4325-80B1-C3A931579AF3"];
        private $terminalMode      = ["pTerminalMode"       => 3];
        private $userAuthenticated = ["isuserAuthenticated" => false];

        private $aviableContentType = [
            "All",
            "RichContent",
            "Gallery",
            "Video",
            "Audio",
            "Live",
        ];

        private $url;


        /**
         * Récupère les derniers articles
         *
         * @param int    $page
         * @param int    $per_page
         * @param string $contentType
         *
         * @return array
         * @throws Exception
         */
        public function getLastContents(int $page = 1, int $per_page = 20, String $contentType = "All") : array {
            if(!in_array($contentType, $this->aviableContentType)) {
                throw new Exception(sprintf("ContentType %s inconnu", $contentType), 400);
            }

            $params = array_merge([
                "page"        => $page,
                "per_page"    => $per_page,
                "contenttype" => $contentType,
            ], $this->appVersion, $this->osVersion, $this->appToken, $this->terminalMode, $this->userAuthenticated);

            $params = http_build_query($params);

            $this->url = $this->base . $this->lastContents . $params;

            return Request::execute($this->url, "lastContents");
        }


        /**
         * Récupère l'article en fonction de son URL
         *
         * @param string $cmsUrl
         *
         * @return array
         * @throws Exception
         */
        public function getOneContent(String $cmsUrl) : array {
            $params = array_merge([
                "cmsurl" => $cmsUrl,
            ], $this->appToken, $this->terminalMode, $this->userAuthenticated);

            $params = http_build_query($params);

            $this->url = $this->base . $this->oneContent . $params;

            return Request::execute($this->url, "oneContent");
        }





        /**
         * Permet de récupéré l'URL de l'API officielle qui est appelé
         *
         * @return String
         */
        public function getUrl() : String {
            return $this->url;
        }
    }