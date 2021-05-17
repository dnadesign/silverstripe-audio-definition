<?php

namespace DNADesign\AudioDefinition\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injector;

class MaoriTranslationService implements TranslationService
{
    use Configurable;

    private static $api_url = 'https://maoridictionary.co.nz/api/1.1/search/';

    private static $api_key = 'uspY2VRCc2394uGb';

    public function getDefinitionAndAudio($wordOrSentence): array
    {
        $uri = self::get_search_url(strtolower($wordOrSentence));

        $info = [
            'definitions' => null,
            'audioSrc' => null
        ];

        try {
            $client = new Client();
            $response = $client->request('GET', $uri);

            $data = $response->getBody()->getContents();
            $json = json_decode($data, true);

            self::set_info_from_api_response($info, $json);
        } catch (ConnectException $e) {
            Injector::inst()->get(LoggerInterface::class)->error($e->getMessage());
        }

        return $info;
    }
    
    /**
     * Build URL to perform the search
     *
     * @param string $keyword
     * @return string
     */
    private static function get_search_url($keyword)
    {
        return Controller::join_links(
            self::config()->get('api_url'),
            '?keywords=' . trim($keyword),
            '?api_key='.self::config()->get('api_key')
        );
    }

    /**
     * Populate the info array with relevant data from the API
     *
     * @param array $info
     * @param array $json
     * @return void
     */
    private static function set_info_from_api_response(&$info, $json)
    {
        $count = (isset($json['total_items'])) ? (int)$json['total_items'] : 0;

        if ($count > 0 && isset($json['data']) && isset($json['data'][0])) {
            // This assumes that the first result in the list is the most relevant
            $data = $json['data'][0];

            if (isset($data['audio'])) {
                $info['audioSrc'] = $data['audio'];
            }

            // Could have multiple definitions
            if (isset($data['definitions'])) {
                $definitions = $data['definitions'];
                if (count($definitions)) {
                    $info['definitions'] = [];
                    foreach ($definitions as $definition) {
                        array_push($info['definitions'], [
                            'id' => isset($definition['id']) ? $definition['id'] : null,
                            'type' => isset($definition['base']) ? $definition['base'] : null,
                            'content' => isset($definition['description']) ? $definition['description'] : null,
                        ]);
                    }
                }
            }
        }
    }
}
