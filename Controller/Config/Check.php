<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Controller\Config;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use WeLoveCustomers\Connector\Helper\Data as WlcHelper;
use WeLoveCustomers\Connector\Service\Api\CheckInstall;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config;

class Check implements HttpPostActionInterface
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
     * @var ProductMetadataInterface
     */
    protected ProductMetadataInterface $productMetadata;

    /**
     * @var ResultFactory
     */
    protected ResultFactory $resultFactory;

    /**
     * @var JsonFactory
     */
    protected JsonFactory $jsonFactory;

    /**
     * @var WriterInterface
     */
    protected WriterInterface $writer;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param WlcHelper $wlcHelper
     * @param CheckInstall $checkInstall
     * @param ProductMetadataInterface $productMetadata
     * @param ResultFactory $resultFactory
     * @param JsonFactory $jsonFactory
     * @param WriterInterface $writer
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        WlcHelper $wlcHelper,
        CheckInstall $checkInstall,
        ProductMetadataInterface $productMetadata,
        ResultFactory $resultFactory,
        JsonFactory $jsonFactory,
        WriterInterface $writer,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->wlcHelper = $wlcHelper;
        $this->checkInstall = $checkInstall;
        $this->productMetadata = $productMetadata;
        $this->resultFactory = $resultFactory;
        $this->jsonFactory = $jsonFactory;
        $this->writer = $writer;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute(): ResultInterface
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        $apiKey = $this->request->getParam('api_key', false);
        $apiGlue = $this->request->getParam('api_glue', false);
        $refreshInstall = $this->request->getParam('refresh_install', false);

        $response = [];
        $response['status'] = 'ko';

        if (!$apiKey || !$apiGlue) {
            $response['message'] = 'Missing Parameters';
            $resultJson->setData($response);
            return $resultJson;
        }

        if ($refreshInstall) {
            $checkInstall = $this->checkInstall->checkInstall($websiteId, [
                'customerKey' => $apiKey,
                'apiGlue' => $apiGlue,
                'installToken' => $refreshInstall
            ]);

            if ((int)$checkInstall['res'] == 1 && (int)$checkInstall['refreshInstall'] == 1) {
                $this->registerConfig($apiKey, $apiGlue, $websiteId);
            }
        }

        if ($apiKey != $this->wlcHelper->getApiKey($websiteId) || $apiGlue != $this->wlcHelper->getApiGlue($websiteId)) {
            $response['message'] = 'Invalid Identifiers';
            $resultJson->setData($response);
            return $resultJson;
        }

        $checkInstall = $this->checkInstall->checkInstall($websiteId);
        if ((int)$checkInstall['res'] == 1) {
            $response['status'] = 'ok';
            $response['framework_type'] = 'Magento2';
            $response['framework_version'] = $this->productMetadata->getVersion();
            $response['php_version'] = phpversion();
            $response['module_version'] = $this->wlcHelper->getModuleVersion();
        }

        $resultJson->setData($response);

        return $resultJson;
    }

    /**
     * @param $apiKey
     * @param $apiGlue
     * @param $websiteId
     * @return void
     */
    private function registerConfig($apiKey, $apiGlue, $websiteId): void
    {
        $this->writer->save(WlcHelper::XML_PATH_WELOVECUSTOMERSCONNECTOR_GENERAL_API_KEY, $apiKey, ScopeInterface::SCOPE_WEBSITES, $websiteId);
        $this->writer->save(WlcHelper::XML_PATH_WELOVECUSTOMERSCONNECTOR_GENERAL_API_GLUE, $apiGlue, ScopeInterface::SCOPE_WEBSITES, $websiteId);

        if ($this->scopeConfig instanceof Config) {
            $this->scopeConfig->clean();
        }
    }
}
