<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreManagerInterface;
use WeLoveCustomers\Connector\Service\Api\AddBuyerApiService;
use WeLoveCustomers\Connector\Service\BuyerFromOrderService;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderInterface;

class OrderObserver implements ObserverInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var AddBuyerApiService
     */
    protected AddBuyerApiService $addBuyerApiService;

    /**
     * @var BuyerFromOrderService
     */
    protected BuyerFromOrderService $buyerFromOrderService;

    /**
     * @param StoreManagerInterface $storeManager
     * @param AddBuyerApiService $addBuyerApiService
     * @param BuyerFromOrderService $buyerFromOrderService
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        AddBuyerApiService $addBuyerApiService,
        BuyerFromOrderService $buyerFromOrderService
    ) {
        $this->storeManager = $storeManager;
        $this->addBuyerApiService = $addBuyerApiService;
        $this->buyerFromOrderService = $buyerFromOrderService;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();

        if ($order->getState() == Order::STATE_PROCESSING) {
            $websiteId = $this->storeManager->getStore($order->getStoreId())->getWebsiteId();
            $this->addBuyerApiService->addBuyer($websiteId, $this->buyerFromOrderService->format($order));
        }
    }
}
