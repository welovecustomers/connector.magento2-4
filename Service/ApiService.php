<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Service;

use WeLoveCustomers\Connector\Helper\Data as WlcHelper;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\Rest\Request;
use GuzzleHttp\Exception\GuzzleException;

class ApiService
{
    /**
     * @var WlcHelper
     */
    protected WlcHelper $wlcHelper;

    /**
     * @var ClientFactory
     */
    private ClientFactory $clientFactory;

    /**
     * @var ResponseFactory
     */
    private ResponseFactory $responseFactory;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @param WlcHelper $wlcHelper
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        WlcHelper $wlcHelper,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        SerializerInterface $serializer
    ) {
        $this->wlcHelper = $wlcHelper;
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->serializer = $serializer;
    }

    /**
     * @param string $endpoint
     * @param $websiteId
     * @param array $params
     * @param string $requestMethod
     * @return mixed
     */
    public function doRequest(string $endpoint, $websiteId, array $params = [], string $requestMethod = Request::METHOD_GET): mixed
    {
        $endpoint = rtrim($endpoint, '/') . '/';

        $client = $this->clientFactory->create([
            'config' => [
                'base_uri' => $this->wlcHelper->getApiUrl(),
            ],
        ]);

        try {
            $auth = [
                'customerKey' => $this->wlcHelper->getApiKey($websiteId),
                'apiGlue' => $this->wlcHelper->getApiGlue($websiteId),
            ];

            $payload = array_merge($auth, $params);
            $response = $client->request(
                $requestMethod,
                $endpoint,
                ['form_params' => $payload]
            );
        } catch (GuzzleException $exception) {
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        try {
            $result = $this->serializer->unserialize($response->getBody());
        } catch (\Exception $e) {
            return false;
        }

        if (!isset($result['res']) || !$result['res']) {
            return false;
        }

        return $result;
    }
}
