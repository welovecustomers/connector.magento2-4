<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const MODULE_NAME = 'WeLoveCustomers_Connector';
    const XML_PATH_WELOVECUSTOMERSCONNECTOR_GENERAL_ENABLED = 'welovecustomersconnector/general/enabled';
    const XML_PATH_WELOVECUSTOMERSCONNECTOR_GENERAL_API_URL = 'welovecustomersconnector/general/api_url';
    const XML_PATH_WELOVECUSTOMERSCONNECTOR_GENERAL_API_KEY = 'welovecustomersconnector/general/api_key';
    const XML_PATH_WELOVECUSTOMERSCONNECTOR_GENERAL_API_GLUE = 'welovecustomersconnector/general/api_glue';
    const XML_PATH_WELOVECUSTOMERSCONNECTOR_GENERAL_TOTAL_FIELD = 'welovecustomersconnector/general/order_total_field';
    const XML_PATH_WELOVECUSTOMERSCONNECTOR_GENERAL_ORDER_IDENTIFIER_FIELD = 'welovecustomersconnector/general/order_identifier_field';
    const XML_PATH_WELOVECUSTOMERSCONNECTOR_GENERAL_SYNC_CUSTOMER = 'welovecustomersconnector/general/sync_customer';

    /**
     * @var ModuleListInterface
     */
    protected ModuleListInterface $moduleList;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @param Context $context
     * @param ModuleListInterface $moduleList
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        ModuleListInterface $moduleList,
        StoreManagerInterface $storeManager
    ) {
        $this->moduleList = $moduleList;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDashboardUrl(): string
    {
        $baseurl = $this->storeManager->getStore()->getBaseUrl();
        $link ='https://www.welovecustomers.fr/solutions/parrainage-e-commerce/?utm_campaign=install-plugin&utm_source=magento2&utm_medium=plugin&utm_term=%s';
        $link = sprintf($link, $baseurl);

        return $link;
    }

    /**
     * @return mixed
     */
    public function getModuleVersion(): mixed
    {
        return $this->moduleList->getOne(self::MODULE_NAME)['setup_version'];
    }

    /**
     * @param $website
     * @return mixed
     */
    public function isEnabled($website = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WELOVECUSTOMERSCONNECTOR_GENERAL_ENABLED, ScopeInterface::SCOPE_WEBSITE, $website);
    }

    /**
     * @return mixed
     */
    public function getApiUrl(): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WELOVECUSTOMERSCONNECTOR_GENERAL_API_URL);
    }

    /**
     * @param $website
     * @return mixed
     */
    public function getApiKey($website = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WELOVECUSTOMERSCONNECTOR_GENERAL_API_KEY, ScopeInterface::SCOPE_WEBSITE, $website);
    }

    /**
     * @param $website
     * @return mixed
     */
    public function getApiGlue($website = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WELOVECUSTOMERSCONNECTOR_GENERAL_API_GLUE, ScopeInterface::SCOPE_WEBSITE, $website);
    }

    /**
     * @param $website
     * @return mixed
     */
    public function getOrderTotalField($website = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WELOVECUSTOMERSCONNECTOR_GENERAL_TOTAL_FIELD, ScopeInterface::SCOPE_WEBSITE, $website);
    }

    /**
     * @param $website
     * @return mixed
     */
    public function getOrderIdentifierField($website = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WELOVECUSTOMERSCONNECTOR_GENERAL_ORDER_IDENTIFIER_FIELD, ScopeInterface::SCOPE_WEBSITE, $website);
    }

    /**
     * @param $website
     * @return mixed
     */
    public function getSyncCustomer($website = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WELOVECUSTOMERSCONNECTOR_GENERAL_SYNC_CUSTOMER, ScopeInterface::SCOPE_WEBSITE, $website);
    }
}
