<?php

    namespace LeProgres\Api;

    use DateTime;

    class Parser {

        private $encoding = "UTF-8";

        /**
         * Parse les retours de l'API officielle en quelque chose de plus mieux bien
         *
         * @param Object $body
         * @param string $type
         *
         * @return array
         */
        public function parse (Object $body, string $type) : array {
            return $this->$type($body->result);
        }


        /**
         * Parse les données des derniers contenus
         *
         * @param array $results
         *
         * @return array
         */
        private function lastContents (array $results) : array {
            $contents = [];

            foreach ($results as $result) {
                $contents[] = $this->oneContent($result);
            }

            return $contents;
        }

        /**
         * Parse les données d'un seul contenu
         *
         * @param Object $result
         *
         * @return array
         */
        private function oneContent (Object $result) : array {
            $content = [];

            $content['key']        = $result->Key;
            $content['created_at'] = $this->generateDate($result->PublicationDate);
            $content['updated_at'] = $this->generateDate($result->ModificationDate);

            $content['image']            = [];
            $content['image']['thumb']   = $result->ImageUrl;
            $content['image']['url']     = (isset($result->Details->Images[0])) ? $result->Details->Images[0]->Url : $result->ImageUrl;
            $content['image']['caption'] = (isset($result->Details->Images[0])) ? $result->Details->Images[0]->Caption : $result->ImageCaption;

            $content['title']       = $result->Details->Title;
            $content['headline']    = ucfirst(mb_strtolower($result->Headline, $this->encoding));
            $content['author']      = $result->Author;
            $content['description'] = $result->ShortText;
            $content['intro']       = $result->Details->StandFirst;
            $content['url']         = $result->WebUrl;
            $content['cmsUrl']      = $result->CmsUrl;

            $content['categories'] = [];
            $content['categories'][] = $result->TagSections[0]->Name;
            if(!empty($result->TagSections[0]->Tags)) {
                $content['categories'][] = $result->TagSections[0]->Tags[0]->Name;
            }

            $content['contentType'] = $result->ContentType;
            $content['contents']    = [];

            switch ($result->ContentType) {
                case "RichContent":
                    if (false === \is_array($result->Details->Components)) {
                        break;
                    }

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
                    if (false === \is_array($result->Details->Components)) {
                        break;
                    }

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


            return $content;
        }


        /**
         * Permet de créer une balise <iframe/>
         *
         * @param string $url
         *
         * @return string
         */
        private function generateIframeTag(string $url) : string {
            return '<iframe src="' . $url . '" frameborder="0" allowfullscreen></iframe>';
        }


        /**
         * Permet de créer une balise <audio/>
         *
         * @param string $url
         *
         * @return string
         */
        private function generateAudioTag(string $url) : string {
            return '<audio src="' . $url . '" controls=""></audio>';
        }

        /**
         * Permet de créer une date bien formatée
         *
         * @param string $datetime
         * @param string $in
         * @param string $out
         *
         * @return string
         */
        private function generateDate(string $datetime, string $in = "YmdHis", string $out = "Y-m-d H:i:s") : string {
            return DateTime::createFromFormat($in, $datetime)->format($out);
        }
    }
