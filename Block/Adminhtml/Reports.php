<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use WeLoveCustomers\Connector\Helper\Data as WlcHelper;
use WeLoveCustomers\Connector\Service\Api\GetStatsApiService;
use WeLoveCustomers\Connector\Service\Api\ReferralInfosApiService;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Directory\Helper\Data as DirectoryHelper;
use WeLoveCustomers\Connector\Service\Api\OfferApiService;

class Reports extends Template
{
    /**
     * @var array|null
     */
    private ?array $referralInfosCache = null;

    /**
     * @var array|null
     */
    private ?array $statsCache = null;

    /**
     * @var WlcHelper
     */
    protected WlcHelper $wlcHelper;

    /**
     * @var ReferralInfosApiService
     */
    protected ReferralInfosApiService $referralInfosApiService;

    /**
     * @var GetStatsApiService
     */
    protected GetStatsApiService $getStatsApiService;

    /**
     * @param Context $context
     * @param WlcHelper $wlcHelper
     * @param ReferralInfosApiService $referralInfosApiService
     * @param GetStatsApiService $getStatsApiService
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        Context $context,
        WlcHelper $wlcHelper,
        ReferralInfosApiService $referralInfosApiService,
        GetStatsApiService $getStatsApiService,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        $this->wlcHelper = $wlcHelper;
        $this->referralInfosApiService = $referralInfosApiService;
        $this->getStatsApiService = $getStatsApiService;
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDashboardUrl(): string
    {
        return $this->wlcHelper->getDashboardUrl();
    }

    /**
     * @return bool
     */
    public function isConfigValid(): bool
    {
        return is_array($this->getReferralInfos());
    }

    /**
     * @return false|int
     */
    public function getWebsiteId()
    {
        $storeId = $this->_request->getParam('store', false);

        if (!$storeId) {
            return false;
        }

        try {
            return $this->_storeManager->getStore($storeId)->getWebsiteId();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getMasterInfo()
    {
        $offer = $this->getReferralInfos();
        return $this->getOfferInfo($offer['masterOffer']);
    }

    /**
     * @return string
     */
    public function getSlaveInfo()
    {
        $offer = $this->getReferralInfos();
        return $this->getOfferInfo($offer['slaveOffer']);
    }

    /**
     * @return mixed|string
     */
    public function getNpsScore()
    {
        $stats = $this->getStats();
        return $stats['npsScore'] ?? '-';
    }

    /**
     * @return mixed
     */
    public function getInvitationNumber()
    {
        $stats = $this->getStats();
        return $stats['contact'];
    }

    /**
     * @return mixed
     */
    public function getFatherNumber()
    {
        $stats = $this->getStats();
        return $stats['father'];
    }

    /**
     * @return mixed
     */
    public function getSonNumber()
    {
        $stats = $this->getStats();
        return $stats['slave'];
    }

    /**
     * @param $value
     * @return string
     */
    public function getSponsoringClass($value): string
    {
        return $value ? "info" : "warning";
    }

    /**
     * @return string
     */
    public function getNPSClass(): string
    {
        $stats = $this->getStats();
        $score = $stats['npsScore'] ?? false;

        if ($score) {
            if ($score > 20) {
                return "success";
            }

            if ($score > -20) {
                return "warning";
            }

            return "danger";
        }

        return "info";
    }

    /**
     * @return float|int|null
     */
    private function getSponringPercent()
    {
        $referralInfos = $this->getReferralInfos();
        $nbSlave = $referralInfos['availableCodeSlave'] ?? false;
        $nbMaster = $referralInfos['availableCodesMaster'] ?? false;

        if ($nbSlave) {
            return ($nbMaster / $nbSlave) * 100;
        }

        return null;
    }

    /**
     * @param $offer
     * @return string
     */
    private function getOfferInfo($offer)
    {
        if ($offer) {
            switch ($offer['offerValueType']) {
                case OfferApiService::TYPE_AMOUNT:
                    return $offer['offerValue'] . "€";
                case OfferApiService::TYPE_PERCENT:
                    return $offer['offerValue'] . "%";
                case OfferApiService::TYPE_FREE_SHIPPING:
                    return "Free shipping";
            }
        }
        return "<p></p>";
    }

    /**
     * @return mixed
     */
    private function getReferralInfos(): mixed
    {
        if (!$this->referralInfosCache) {
            $apiResponse = $this->referralInfosApiService->findReferralInfos($this->getWebsiteId());
            if ($apiResponse) {
                $this->referralInfosCache = $apiResponse;
            }
        }

        return $this->referralInfosCache;
    }

    /**
     * @return mixed
     */
    private function getStats(): mixed
    {
        if (!$this->statsCache) {
            $apiResponse = $this->getStatsApiService->getStats($this->getWebsiteId());
            if ($apiResponse) {
                $this->statsCache = $apiResponse;
            }
        }

        return $this->statsCache['stats'];
    }
}
