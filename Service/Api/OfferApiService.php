<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Service\Api;

use WeLoveCustomers\Connector\Service\ApiService;
use Magento\Framework\Webapi\Rest\Request;

class OfferApiService
{
    const TYPE_PERCENT = "percent";
    const TYPE_FREE_SHIPPING = "freeshipping";
    const TYPE_AMOUNT = 'amount';

    const ENDPOINT_CHECK_OFFER_CODE = 'checkOfferCode';

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
     * @param $websiteId
     * @param $couponCode
     * @return mixed
     */
    public function findOfferByCode($websiteId, $couponCode): mixed
    {
        return $this->apiService->doRequest(self::ENDPOINT_CHECK_OFFER_CODE, $websiteId, [
            'inputCode' => $couponCode
        ], Request::HTTP_METHOD_POST);
    }
}
