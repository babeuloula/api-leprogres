<?php

    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\GuzzleException;

    class LeProgres {

        private $apiRoute = "http://www.leprogres.fr/app_mobile/";

        private $lastContents = "listcontentbytype?";

        private $params = [
            "appversion"          => "2.9.1",
            "osversion"           => "7.0.1",
            "pTokenApplication"   => "8C841AA5-33C3-4325-80B1-C3A931579AF3",
            "pTerminalMode"       => 3,
            "page"                => 1,
            "per_page"            => 20,
            "contenttype"         => "All",
            "isuserAuthenticated" => true,
        ];

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
         * @return mixed
         * @throws Exception
         */
        public function getLastContents($page = 1, $per_page = 20, $contentType = "All") {
            if(!in_array($contentType, $this->aviableContentType)) {
                throw new Exception(sprintf("ContentType %s inconnu", $contentType));
            }

            $params = array_merge($this->params, [
                "page"        => $page,
                "per_page"    => $per_page,
                "contenttype" => $contentType,
            ]);

            $params = http_build_query($params);

            $this->url = $this->apiRoute . $this->lastContents . $params;

            return $this->getContents($this->url);
        }



        public function getUrl() {
            return $this->url;
        }



        private function getContents($url) {
            $client = new Client();

            try {
                $res = $client->request('GET', $url);
            } catch (GuzzleException $e) {
                throw new Exception($e->getMessage());
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

            $body = json_decode($res->getBody()->getContents());

            if(json_last_error() !== JSON_ERROR_NONE || !$body->success) {
                throw new Exception("Erreur lors de la récupération des contenus");
            }

            $body = $this->parseContents($body);

            return $body;
        }



        private function parseContents($body) {
            $contents = [];

            foreach ($body->result as $result) {
                $content = [];

                $content['key']        = $result->Key;
                $content['created_at'] = $this->generateDateTime($result->PublicationDate);
                $content['updated_at'] = $this->generateDateTime($result->ModificationDate);

                $content['image']            = [];
                $content['image']['thumb']   = $result->ImageUrl;
                $content['image']['url']     = (isset($result->Details->Images[0])) ? $result->Details->Images[0]->Url : $result->ImageUrl;
                $content['image']['caption'] = (isset($result->Details->Images[0])) ? $result->Details->Images[0]->Caption : $result->ImageCaption;

                $content['title']       = $result->Details->Title;
                $content['headline']    = ucfirst(strtolower($result->Headline));
                $content['author']      = $result->Author;
                $content['description'] = $result->ShortText;
                $content['intro']       = $result->Details->StandFirst;
                $content['url']         = $result->WebUrl;

                $content['categories'] = [];
                $content['categories'][] = $result->TagSections[0]->Name;
                if(!empty($result->TagSections[0]->Tags)) {
                    $content['categories'][] = $result->TagSections[0]->Tags[0]->Name;
                }

                $content['contentType'] = $result->ContentType;
                $content['contents']    = [];

                switch ($result->ContentType) {
                    case "RichContent":
                        foreach ($result->Details->Components as $component) {
                            $c = [];

                            switch ($component->Type) {
                                case "FreeHtmlComponent":
                                    $c['content'] = $component->Source;
                                    break;

                                case "TextComponent":
                                    $c['content'] = $component->Content;
                                    break;

                                case "GoogleComponent":
                                    $c['content'] = $this->generateIframeTag($component->Url);
                                    $c['url']     = $component->Url;

                                    $map               = [];
                                    $map['query']      = $component->MapData->Query;
                                    $map['center_lat'] = $component->MapData->CenterLatitude;
                                    $map['center_lng'] = $component->MapData->CenterLongitude;
                                    $map['zoom']       = $component->MapData->ZoomLevel;
                                    $map['type']       = $component->MapData->MapType;

                                    $c['map'] = $map;
                                    break;

                                case "TwitterComponent":
                                    $c['url'] = $component->Url;
                                    $c['content'] = $component->ExternalContent;

                                    $t             = [];
                                    $t['id']       = $component->TwitterData->TweetId;
                                    $t['type']     = $component->TwitterData->Type;
                                    $t['username'] = $component->TwitterData->Username;

                                    $c['twitter'] = $t;
                                    break;

                                case "VideoComponent":
                                    $c['provider'] = $component->ProviderName;
                                    $c['id'] = $component->VideoId;
                                    $c['url'] = $component->Source;
                                    $c['content'] = $this->generateIframeTag($component->Source);

                                    $i            = [];
                                    $i['url']     = $component->Image->Url;
                                    $i['caption'] = $component->Image->Caption;

                                    $c['image'] = $i;
                                    break;

                                case "InsetComponent":
                                    $c['title']   = $component->Title;
                                    $c['content'] = $component->Content;
                                    break;

                                case "ImagesComponent":
                                    $c['images'] = [];

                                    foreach ($component->Images as $image) {
                                        $i            = [];
                                        $i['url']     = $image->Url;
                                        $i['caption'] = $image->Caption;

                                        $c['images'][] = $i;
                                    }
                                    break;
                            }

                            $c['type'] = $component->Type;

                            $content['contents'][] = $c;
                        }
                        break;

                    case "Gallery";
                        foreach ($result->Details->Images as $image) {
                            $i            = [];
                            $i['url']     = $image->Url;
                            $i['caption'] = $image->Caption;

                            $content['contents'][] = $i;
                        }
                        break;

                    case "Video";
                        $c             = [];
                        $c['content']  = $this->generateIframeTag($result->Details->Source);
                        $c['provider'] = $result->Details->ProviderName;
                        $c['id']       = $result->Details->ProviderId;
                        $c['url']      = $result->Details->Source;

                        $content['contents'][] = $c;
                        break;

                    case "Audio":
                        $c            = [];
                        $c['content'] = $this->generateAudioTag($result->Details->Source);
                        $c['url']     = $result->Details->Source;

                        $content['contents'][] = $c;
                        break;

                    default:
                        break;
                }


                $contents[] = $content;
            }

            return $contents;
        }


        /**
         * @param String $url
         *
         * @return string
         */
        private function generateIframeTag($url) {
            return '<iframe src="' . $url . '" frameborder="0" allowfullscreen></iframe>';
        }

        /**
         * @param String $url
         *
         * @return string
         */
        private function generateAudioTag($url) {
            return '<audio src="' . $url . '" controls=""></audio>';
        }

        private function generateDateTime($datetime) {
            return DateTime::createFromFormat("YmdHis", $datetime)->format("Y-m-d H:i:s");
        }

    }