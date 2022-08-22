<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service for make request to rest endpoints
 */
class HttpRequestService
{
    /**
     * @var HttpClientInterface
     */
    private HttpClientInterface $httpClient;

    /**
     * @var ContainerBagInterface
     */
    private ContainerBagInterface $params;

    /**
     * @param HttpClientInterface $httpClient
     * @param ContainerBagInterface $params
     */
    public function __construct(HttpClientInterface $httpClient, ContainerBagInterface $params)
    {
        $this->httpClient = $httpClient;
        $this->params = $params;
    }

    /**
     * @return string[]
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getCountriesList(): array
    {
        $countriesListEndpoint = $this->createEndpoint($this->params->get('api')['endpoints']['all_countries']);
        $errorMessage = 'Ha ocurrido un error al buscar el listado de paises.';
        $countries = $this->makeRequest('GET', $countriesListEndpoint, [], $errorMessage, 200);
        $filterCountries = [];
        foreach($countries as $item){
            $filterCountries[$item['cca2']] = $item['name']['common'];
        }
        return $filterCountries;
    }

    /**
     * @param string $code
     * @return array|string|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getCountryByCode(string $code): ?array
    {
        $endpoint = $this->createEndpoint($this->params->get('api')['endpoints']['by_code']);
        $endpoint = str_replace('{code}', $code, $endpoint);
        $errorMessage = 'Ha ocurrido un error al buscar la informacion del pais a partir del codigo.';
        $country = $this->makeRequest('GET', $endpoint, [], $errorMessage, 200);
        if(!empty($country)){
            return $country[0];
        }

        return null;
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @param string $errorMessage
     * @param int $status
     * @return string[]
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function makeRequest(string $method, string $endpoint, array $data, string $errorMessage, int $status): array
    {
        try {
            $response = $this->httpClient->request($method, $endpoint, [
                'base_uri' => $this->params->get('api')['url'],
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => $data
            ]);

            if ($response->getStatusCode() === $status) {
                return $response->toArray();
            }

            return [
                'status' => 'KO',
                'message' => $errorMessage
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'KO',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * @param string $endpoint
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function createEndpoint(string $endpoint): string
    {
        $version = '/'.$this->params->get('api')['version'];
        return $version.$endpoint;
    }

}