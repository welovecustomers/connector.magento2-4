<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Observer;

use Magento\Framework\Event\ObserverInterface;
use WeLoveCustomers\Connector\Helper\Data as WlcHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Model\RuleFactory;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use WeLoveCustomers\Connector\Service\CreateCouponFromOfferService;
use WeLoveCustomers\Connector\Service\Api\OfferApiService;
use Magento\Framework\Event\Observer;

class CouponObserver implements ObserverInterface
{
    /**
     * @var WlcHelper
     */
    protected WlcHelper $wlcHelper;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var RuleFactory
     */
    protected RuleFactory $ruleFactory;

    /**
     * @var RuleRepositoryInterface
     */
    protected RuleRepositoryInterface $ruleRepository;

    /**
     * @var CouponInterface
     */
    protected CouponInterface $coupon;

    /**
     * @var CreateCouponFromOfferService
     */
    protected CreateCouponFromOfferService $createCouponFromOfferService;

    /**
     * @var OfferApiService
     */
    protected OfferApiService $offerApiService;

    /**
     * @param WlcHelper $wlcHelper
     * @param StoreManagerInterface $storeManager
     * @param CouponInterface $coupon
     * @param RuleFactory $ruleFactory
     * @param RuleRepositoryInterface $ruleRepository
     * @param CreateCouponFromOfferService $createCouponFromOfferService
     * @param OfferApiService $offerApiService
     */
    public function __construct(
        WlcHelper $wlcHelper,
        StoreManagerInterface $storeManager,
        CouponInterface $coupon,
        RuleFactory $ruleFactory,
        RuleRepositoryInterface $ruleRepository,
        CreateCouponFromOfferService $createCouponFromOfferService,
        OfferApiService $offerApiService
    ) {
        $this->wlcHelper = $wlcHelper;
        $this->storeManager = $storeManager;
        $this->coupon = $coupon;
        $this->ruleFactory = $ruleFactory;
        $this->ruleRepository = $ruleRepository;
        $this->createCouponFromOfferService = $createCouponFromOfferService;
        $this->offerApiService = $offerApiService;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        try {
            $websiteId = $this->storeManager->getWebsite()->getId();

            if (!$this->wlcHelper->isEnabled($websiteId)) {
                return;
            }

            $controller = $observer->getControllerAction();
            $couponCode = $controller->getRequest()->getParam('coupon_code', false);

            if ($couponCode) {
                $coupon = $this->coupon->loadByCode($couponCode);
                if (!$coupon->getRuleId()) {
                    $offerResponse = $this->offerApiService->findOfferByCode($websiteId, $couponCode);
                    if ($offerResponse) {
                        $offerType = strtolower($offerResponse['offerType']) == 'f' ? 'fOffer' : 'pOffer';
                        if (!isset($offerResponse[$offerType])) {
                            return;
                        }

                        $offer = $offerResponse[$offerType];
                        $this->createCouponFromOfferService->execute($offer, $couponCode);
                    }
                } else {
                    $ruleId = $coupon->getRuleId();
                    $rule = $this->ruleFactory->create();
                    $rule->load($ruleId);
                    if ($rule->getFromWlc()) {
                        $offerResponse = $this->offerApiService->findOfferByCode($websiteId, $couponCode);
                        if (!$offerResponse) {
                            $this->ruleRepository->deleteById($ruleId);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return;
        }
    }
}
