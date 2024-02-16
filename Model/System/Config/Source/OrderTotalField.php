<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class OrderTotalField implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Grand Total',
                'value' => 'grand_total',
            ],
            [
                'label' => 'Subtotal',
                'value' => 'subtotal',
            ],
        ];
    }
}
