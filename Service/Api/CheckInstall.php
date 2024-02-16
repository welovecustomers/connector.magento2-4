<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Service\Api;

use WeLoveCustomers\Connector\Service\ApiService;
use Magento\Framework\Webapi\Rest\Request;

class CheckInstall
{
    const ENDPOINT_CHECK_INSTALL = 'checkInstall';

    /**
     * @var ApiService
     */
    protected ApiService $apiService;

    /**
     * @param ApiService $apiService
     */
    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * @param array $params
     * @param $websiteId
     * @return mixed
     */
    public function checkInstall($websiteId, array $params = []): mixed
    {
        return $this->apiService->doRequest(self::ENDPOINT_CHECK_INSTALL, $websiteId, $params, Request::HTTP_METHOD_POST);
    }
}
