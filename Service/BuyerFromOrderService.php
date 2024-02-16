<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Service;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use WeLoveCustomers\Connector\Helper\Data as WlcHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Locale\Resolver;

class BuyerFromOrderService
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
     * @var Resolver
     */
    protected Resolver $resolver;

    /**
     * @param WlcHelper $wlcHelper
     * @param StoreManagerInterface $storeManager
     * @param Resolver $resolver
     */
    public function __construct(
        WlcHelper $wlcHelper,
        StoreManagerInterface $storeManager,
        Resolver $resolver
    ) {
        $this->wlcHelper = $wlcHelper;
        $this->storeManager = $storeManager;
        $this->resolver = $resolver;
    }

    /**
     * @param OrderInterface $order
     * @return array
     * @throws NoSuchEntityException
     */
    public function format(OrderInterface $order): array
    {
        $websiteId = $this->getWebsiteId($order);

        return [
            'customer-key' => $this->wlcHelper->getApiKey($websiteId),
            'data-name' => $this->getCustomerFullName($order),
            'data-firstname' => $order->getCustomerFirstname(),
            'data-lastname' => $order->getCustomerLastname(),
            'data-email' => $order->getCustomerEmail(),
            'data-mobile' => $order->getBillingAddress()->getTelephone(),
            'data-amount' => $this->getOrderTotal($order),
            'data-coupons' => $order->getCouponCode(),
            'data-timestamp' => $this->getTimestamp($order),
            'data-purchase-id' => $this->getPurchaseId($order),
            'data-hash' => $this->getHash($order),
            'data-lang' => $this->resolver->getLocale(),
            'data-currency' => $order->getOrderCurrencyCode(),
            'data-external-id' => $this->getExternalId($order, $websiteId),
        ];
    }

    /**
     * @param OrderInterface $order
     * @return string
     * @throws NoSuchEntityException
     */
    public function getHash(OrderInterface $order): string
    {
        $apiGlue = $this->wlcHelper->getApiGlue($this->getWebsiteId($order));

        $hashFields = [
            $apiGlue,
            $this->getCustomerFullName($order),
            $order->getCustomerEmail(),
            $order->getBillingAddress()->getTelephone(),
            $this->getOrderTotal($order),
            $order->getCouponCode(),
            $this->getTimestamp($order),
            $this->getPurchaseId($order),
        ];

        $dataToHash = implode('', $hashFields);

        return md5($dataToHash);
    }

    /**
     * @param OrderInterface $order
     * @return int
     * @throws NoSuchEntityException
     */
    public function getWebsiteId(OrderInterface $order): int
    {
        return (int)$this->storeManager->getStore($order->getStoreId())->getWebsiteId();
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function getCustomerFullName(OrderInterface $order): string
    {
        return $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
    }

    /**
     * @param OrderInterface $order
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getOrderTotal(OrderInterface $order): mixed
    {
        $orderTotalField = $this->wlcHelper->getOrderTotalField($this->getWebsiteId($order));
        return number_format((float)$order->getData($orderTotalField ?? 'grand_total'), 2);
    }

    /**
     * @param OrderInterface $order
     * @return int
     */
    public function getTimestamp(OrderInterface $order): int
    {
        return $order->getCreatedAt() ? strtotime($order->getCreatedAt()) : time();
    }

    /**
     * @param OrderInterface $order
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getPurchaseId(OrderInterface $order): mixed
    {
        $orderIdField = $this->wlcHelper->getOrderIdentifierField($this->getWebsiteId($order));
        return $order->getData($orderIdField ?? 'entity_id');
    }

    /**
     * @param OrderInterface $order
     * @param $websiteId
     * @return string
     */
    public function getExternalId(OrderInterface $order, $websiteId): string
    {
        return $this->wlcHelper->getSyncCustomer($websiteId) ? (string)$order->getCustomerId() : '';
    }
}
