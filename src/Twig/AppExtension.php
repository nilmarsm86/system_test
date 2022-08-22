<?php

namespace App\Twig;

use App\Service\HttpRequestService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * App Twig extensions
 */
class AppExtension extends AbstractExtension
{
    /**
     * @var HttpRequestService
     */
    private HttpRequestService $httpRequestService;

    /**
     * @param HttpRequestService $httpRequestService
     */
    public function __construct(HttpRequestService $httpRequestService)
    {
        $this->httpRequestService = $httpRequestService;
    }

    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('country_by_code', [$this, 'countryByCode']),
        ];
    }

    /**
     * Function for get name of country by code
     * @param string $code
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function countryByCode(string $code): string
    {
        $countryData = $this->httpRequestService->getCountryByCode($code);
        return $countryData['name']['common'];
    }
}