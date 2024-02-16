<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class OrderIdentifierField implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Increment ID',
                'value' => 'increment_id',
            ],
            [
                'label' => 'Entity ID (SGBD auto-increment)',
                'value' => 'entity_id',
            ],
        ];
    }
}
