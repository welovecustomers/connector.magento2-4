<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Controller\Purchase;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\StoreManagerInterface;
use WeLoveCustomers\Connector\Helper\Data as WlcHelper;
use WeLoveCustomers\Connector\Service\Api\CheckInstall;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Details implements HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var WlcHelper
     */
    protected WlcHelper $wlcHelper;

    /**
     * @var CheckInstall
     */
    protected CheckInstall $checkInstall;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var OrderInterface
     */
    protected OrderInterface $order;

    /**
     * @var ResultFactory
     */
    protected ResultFactory $resultFactory;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $jsonFactory;

    /**
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param WlcHelper $wlcHelper
     * @param CheckInstall $checkInstall
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderInterface $order
     * @param ResultFactory $resultFactory
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        WlcHelper $wlcHelper,
        CheckInstall $checkInstall,
        OrderRepositoryInterface $orderRepository,
        OrderInterface $order,
        ResultFactory $resultFactory,
        JsonFactory $jsonFactory
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->wlcHelper = $wlcHelper;
        $this->checkInstall = $checkInstall;
        $this->orderRepository = $orderRepository;
        $this->order = $order;
        $this->resultFactory = $resultFactory;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(): ResultInterface
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        $apiKey = $this->request->getParam('apikey', false);
        $apiGlue = $this->request->getParam('apiglue', false);
        $orderId = $this->request->getParam('orderid', false);

        $response = [];
        $response['status'] = 'ko';

        if (!$apiKey || !$apiGlue || !$orderId) {
            $response['message'] = 'Missing Parameters';
            $resultJson->setData($response);
            return $resultJson;
        }

        if ($apiKey != $this->wlcHelper->getApiKey($websiteId) || $apiGlue != $this->wlcHelper->getApiGlue($websiteId)) {
            $response['message'] = 'Invalid Identifiers';
            $resultJson->setData($response);
            return $resultJson;
        }

        $checkInstall = $this->checkInstall->checkInstall($websiteId);
        if ((int)$checkInstall['res'] != 1) {
            $response['message'] = 'Missing Parameters';
            $resultJson->setData($response);
            return $resultJson;
        }

        try {
            $orderIdentifierField = $this->wlcHelper->getOrderIdentifierField($websiteId);
            $order = $this->order->loadByAttribute($orderIdentifierField, $orderId);

            if (!$order->getEntityId()) {
                $response['message'] = 'Order was not found';
                $resultJson->setData($response);
                return $resultJson;
            }

            $data['order'] = $order->getData($orderIdentifierField);
            $data['status'] = $order->getState();

            $i = 0;
            foreach ($order->getItems() as $item) {
                $data['products'][$i]['name'] = $item->getData('name');
                $data['products'][$i]['sku'] = $item->getData('sku');
                $data['products'][$i]['qty'] = $item->getData('qty_ordered');
                $data['products'][$i]['pricePerUnit'] = $item->getData('base_price');
                $data['products'][$i]['pricePerUnitWithTax'] = $item->getData('base_price_incl_tax') - $item->getData('base_discount_amount');
                $i++;
            }
            $resultJson->setData($data);
            return $resultJson;
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        $resultJson->setData($response);
        return $resultJson;
    }
}
