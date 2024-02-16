<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\ViewModel\Checkout\Onepage\Success;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use WeLoveCustomers\Connector\Helper\Data as WlcHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use WeLoveCustomers\Connector\Service\BuyerFromOrderService;

class TagViewModel implements ArgumentInterface
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
     * @var BuyerFromOrderService
     */
    protected BuyerFromOrderService $buyerFromOrderService;

    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;

    /**
     * @param WlcHelper $wlcHelper
     * @param StoreManagerInterface $storeManager
     * @param BuyerFromOrderService $buyerFromOrderService
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        WlcHelper $wlcHelper,
        StoreManagerInterface $storeManager,
        BuyerFromOrderService $buyerFromOrderService,
        CheckoutSession $checkoutSession
    ) {
        $this->wlcHelper = $wlcHelper;
        $this->storeManager = $storeManager;
        $this->buyerFromOrderService = $buyerFromOrderService;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        try {
            $websiteId = $this->storeManager->getWebsite()->getId();
            return $this->wlcHelper->isEnabled($websiteId)
                && $this->wlcHelper->getApiKey($websiteId)
                && $this->wlcHelper->getApiGlue($websiteId);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getWlcPurchaseData(): array
    {
        $order = $this->checkoutSession->getLastRealOrder();

        try {
            return $this->buyerFromOrderService->format($order);
        } catch (\Exception $e) {
            return [];
        }
    }
}
