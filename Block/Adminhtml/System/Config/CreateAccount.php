<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use WeLoveCustomers\Connector\Helper\Data as WlcHelper;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\NoSuchEntityException;

class CreateAccount extends Field
{
    /**
     * @var WlcHelper
     */
    protected WlcHelper $wlcHelper;

    /**
     * @param Context $context
     * @param WlcHelper $wlcHelper
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        WlcHelper $wlcHelper,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->wlcHelper = $wlcHelper;
        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return __('Please create your account using this link: ') . '<a href="' . $this->getLink() . '" target="_blank"> '.__('Create my referral program').'</a>';
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getLink(): string
    {
        return $this->wlcHelper->getDashboardUrl();
    }
}
