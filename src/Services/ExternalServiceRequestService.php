<?php

namespace Cirelramos\ExternalRequest\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;

/**
 *
 */
class ExternalServiceRequestService
{
    /**
     * @param $baseUri
     * @param $method
     * @param string $requestPath
     * @param array $formParams
     * @param array $headers
     * @param string $modeParams
     * @param bool $async
     * @param bool $pureResponse
     * @return mixed
     * @throws GuzzleException
     */
    public static function execute(
        $baseUri,
        $method,
        $requestPath = '',
        $formParams = [],
        $headers = [],
        $modeParams = 'form_params',
        $async = false,
        $pureResponse = false
    ) {
        $client = new Client(
            [
                'base_uri' => $baseUri,
                'curl'     => [
                    CURLOPT_SSL_VERIFYPEER => false,
                ],
            ]
        );

        /** @var Request $request */
        $request = request();
        foreach (config('external-request.default_parameters_to_header') as $key => $item) {
            if ($request->$item) {
                $headers[ $key ] = $item;
            }
        }
        foreach (config('external-request.get_special_values_from_header') as $key => $item) {
            if ($request->$item) {
                $headers[ $key ] = $request->header($item);
            }
        }
        foreach (config('external-request.get_special_values_from_request') as $key => $item) {
            if ($request->$item) {
                $formParams[ $key ] = $request->$item;
            }
        }

        $formAndHeader = [
            $modeParams => $formParams,
            'headers'   => $headers,
        ];


        if($async === true){
            $formAndHeader[ 'timeout' ] = 0.4;
            $formAndHeader[ 'connect_timeout' ] = 0.4;

            try {
                return $client->request($method, $requestPath, $formAndHeader);
            }catch (Exception $exception){
                return true;
            }
        }

        $response = $client->request($method, $requestPath, $formAndHeader);

        if($pureResponse === true){
            return $response;
        }

        $content = $response->getBody()
            ->getContents();

        return json_decode($content, true);

    }
}
