<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use WeLoveCustomers\Connector\Helper\Data as WlcHelper;
use WeLoveCustomers\Connector\Service\Api\MagentoPluginVersionApiService;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Version extends Field
{
    /**
     * @var WlcHelper
     */
    protected WlcHelper $wlcHelper;

    /**
     * @var MagentoPluginVersionApiService
     */
    protected MagentoPluginVersionApiService $magentoPluginVersionApiService;

    /**
     * @param Context $context
     * @param WlcHelper $wlcHelper
     * @param MagentoPluginVersionApiService $magentoPluginVersionApiService
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        WlcHelper $wlcHelper,
        MagentoPluginVersionApiService $magentoPluginVersionApiService,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->wlcHelper = $wlcHelper;
        $this->magentoPluginVersionApiService = $magentoPluginVersionApiService;
        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * @param AbstractElement $element
     * @return \Magento\Framework\Phrase|string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $currentWebsite = $this->_request->getParam('website', 0);
        $apiResponse = $this->magentoPluginVersionApiService->getVersion($currentWebsite);

        $version = $apiResponse['version'] ?? '';

        return __('Module version %1 (latest  version available: %2)', $this->wlcHelper->getModuleVersion(), $version);
    }
}
