<?php
/**
 * Craft Youtube plugin for Craft CMS 3.x
 *
 * Add youtube field
 *
 * @link      https://apt.no/
 * @copyright Copyright (c) 2018 Thomas Sømoen
 */

namespace apt\craftyoutube\services;

use apt\craftyoutube\CraftYoutube;
use apt\craftyoutube\models\Film;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

use Craft;
use craft\base\Component;
use LitEmoji\LitEmoji;

/**
 * @author    Thomas Sømoen
 * @package   CraftYoutube
 * @since     1.0.0
 */
class Youtube extends Component
{
    protected function cleanTitle($title) : string
    {
        $title = LitEmoji::encodeShortcode($title);
        $title = strip_tags($title);

        /* Prevent uppercase titles */
        $title = preg_replace_callback('/([.!?])\s*(\w)/', function ($matches) {
            return strtoupper($matches[1] . ' ' . $matches[2]);
        }, ucfirst(mb_strtolower($title)));

        return $title;
    }

    protected function cleanDescription($description) : string
    {
        $description = strip_tags($description);
        $description = LitEmoji::encodeShortcode($description);

        return $description;
    }

    public function get($post, $required = [])
    {
        $prev = $post['prev'];
        unset($post['prev']);

        if (array_key_exists('title', $post) && empty($post['title'])) {
            unset($post['title']);
        }

        if (array_key_exists('description', $post) && empty($post['description'])) {
            unset($post['description']);
        }

        if (array_key_exists('thumbnails', $post) && !empty($post['thumbnails'])) {
            $post['thumbnails'] = json_decode($post['thumbnails'], true);
        }

        if (!array_key_exists('url', $post)) {
            $youtube = new Film();
            $youtube->setRequired($required);
            $youtube->validate();
            return $youtube;
        }

        if (array_key_exists('url', $post) && empty($post['url'])) {
            $youtube = new Film();
            $youtube->setRequired($required);
            $youtube->validate();
            return $youtube;
        }

        if (!empty($prev) && $prev == $post['url'] && !empty($post['code'])) {

            if (array_key_exists('title', $post)) {
                $post['title'] = $this->cleanTitle($post['title']);
            }

            if (array_key_exists('description', $post)) {
                $post['description'] = $this->cleanDescription($post['description']);
            }

            $youtube = new Film($post);
            $youtube->setRequired($required);
            return $youtube;
        }

        $code = $this->parseUrl($post['url']);

        $youtube = new Film($post);
        $youtube->setRequired($required);

        if (strlen($code) !== 11) {
            $youtube->addError('url', Craft::t('craft-youtube', 'Invalid Youtube url.'));
            return $youtube;
        }

        $youtube->code = $code;

        $useApi = CraftYoutube::getInstance()->settings->getUseApi();

        if (!$useApi) {
            return $youtube;
        }

        $apiKey = CraftYoutube::getInstance()->settings->getApiKey();

        try {
            $headers = ['Referer' => Craft::$app->request->hostName];
            $client = new Client([
                'base_uri' => 'https://www.googleapis.com',
                'headers' => $headers,
            ]);
            $response = $client->get("/youtube/v3/videos?key={$apiKey}&id={$code}&part=snippet,contentDetails");

            $status = $response->getStatusCode();
            if ($status !== 200) {
                throw new \Exception("An error occurred fetching the youtube movie", 1);
            }

            $response = json_decode($response->getBody(), true);

            unset($post['code']);
            unset($post['thumbnails']);
            unset($post['duration']);

            if ($response['pageInfo']['totalResults'] === 0) {
                $youtube->addError('url', Craft::t('craft-youtube', 'Youtube movie "{code}" doesn\'t exist', [ 'code' => $code ]));
                $youtube->code = null;
            } else {
                if (array_key_exists('items', $response)) {
                    $items = array_shift($response['items']);
                    if ($items && count($items) > 0) {
                        $data = [];
                        if (array_key_exists('snippet', $items)) {
                            $data['title'] = $this->cleanTitle($items['snippet']['title']);

                            if (array_key_exists('description', $items['snippet'])) {
                                $data['description'] = $this->cleanDescription($items['snippet']['description']);
                            }
                            if (array_key_exists('thumbnails', $items['snippet'])) {
                                $data['thumbnails'] = $items['snippet']['thumbnails'];
                            }
                        }
                        if (array_key_exists('contentDetails', $items)) {
                            $data['duration'] = $items['contentDetails']['duration'];
                        }

                        if (array_key_exists('title', $post)) {
                            $post['title'] = $this->cleanTitle($post['title']);
                        }

                        if (array_key_exists('description', $post)) {
                            $post['description'] = $this->cleanDescription($post['description']);
                        }

                        $data = array_merge($data, $post);

                        $youtube->setAttributes($data);
                        $youtube->validate();
                    } else {
                        $youtube->addError('url', Craft::t('craft-youtube', 'Youtube movie "{code}" doesn\'t exist', [ 'code' => $code ]));
                    }
                }
            }
        } catch(ClientException $e) {
            $message = 'An error occurred fetching the youtube movie';
            if (Craft::$app->user->isAdmin) {
                $messageAttributes = [
                    'apiKey' => $apiKey,
                ];
                try {
                    $response = $e->getResponse();
                    $response = json_decode($response->getBody(), true);
                    if ($response && isset($response['error']) && isset($response['error']['errors'])) {
                        $error = null;
                        if (is_array($response['error']['errors'])) {
                            $error = array_shift($response['error']['errors']);
                            $message = 'Youtube error';
                            $messageAttributes = array_merge($messageAttributes, $error);
                        }
                    }
                } catch (\Exception $e) {
                    $message = $e->getMessage();
                }
                $youtube->addError('url', Craft::t('craft-youtube', $message, $messageAttributes));
            } else {
                $youtube->addError('url', Craft::t('craft-youtube', $message));
            }
        } catch (\Exception $e) {
            if (Craft::$app->user->isAdmin) {
                $youtube->addError('url', Craft::t('craft-youtube', $e->getMessage()));
            } else {
                $youtube->addError('url', Craft::t('craft-youtube', 'An error occurred fetching the youtube movie'));
            }
        }

        return $youtube;
    }

    public static function parseUrl($param='')
    {
        $param = trim($param);
        if (empty($param)) {
            return;
        }

        // ren videokode
        if(strlen($param) == 11) {
            return self::cleanIt($param);
        }

        // https://gist.github.com/TrevorJTClarke/a14c37db3c11ee23a700
        $pattern = '/^(?:https?:\/\/)?(?:i\.|www\.|img\.)?(?:youtu\.be\/|youtube\.com\/|ytimg\.com\/)(?:embed\/|v\/|vi\/|vi_webp\/|watch\?v=|watch\?.+&v=)((\w|-){11})(?:\S+)?$/i';
        if (preg_match($pattern, $param, $match)) {
            return self::cleanIt($match[1]);
        }

        return null;
    }

    private static function cleanIt($value=null)
    {
        return str_replace(["."], '', trim($value));
    }

    /*
     * @return STRING
     */
     public function formatDuration($ISO8601)
     {
         try {
             $interval = new \DateInterval($ISO8601);
             $duration = str_pad($interval->i, 2, '0', STR_PAD_LEFT).':'.str_pad($interval->s, 2, '0', STR_PAD_LEFT);
             if ($interval->h > 0) {
                 $duration = str_pad($interval->h, 2, '0', STR_PAD_LEFT).':'.$duration;
             }
             if ($interval->d > 0) {
                 $duration = str_pad($interval->d, 2, '0', STR_PAD_LEFT).':'.$duration;
             }
             if ($interval->m > 0) {
                 $duration = str_pad($interval->m, 2, '0', STR_PAD_LEFT).':'.$duration;
             }
             if ($interval->y > 0) {
                 $duration = str_pad($interval->y, 2, '0', STR_PAD_LEFT).':'.$duration;
             }
             return $duration;
         } catch (\Exception $e) {}
         return $ISO8601;
     }
}
