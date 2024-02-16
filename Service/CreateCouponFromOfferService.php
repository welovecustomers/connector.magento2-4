<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Service;

use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupCollectionFactory;
use Magento\SalesRule\Model\RuleFactory;
use WeLoveCustomers\Connector\Service\Api\OfferApiService;

class CreateCouponFromOfferService
{
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var CustomerGroupCollectionFactory
     */
    protected CustomerGroupCollectionFactory $customerGroupCollectionFactory;

    /**
     * @var RuleFactory
     */
    protected RuleFactory $ruleFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CustomerGroupCollectionFactory $customerGroupCollectionFactory
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CustomerGroupCollectionFactory $customerGroupCollectionFactory,
        RuleFactory $ruleFactory,
    ) {
        $this->storeManager = $storeManager;
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * @param array $offer
     * @param $couponCode
     * @return void
     */
    public function execute(array $offer, $couponCode): void
    {
        $rule = $this->ruleFactory->create();

        // fix code linked to a contact
        $uses_per_coupon = 1;
        if (isset($offer['isContactCode']) && $offer['isContactCode'] == true) $uses_per_coupon = 1000;


        $rule->loadPost([
            'rule_id' => null,
            'name' => $offer['title'],
            'description' => strip_tags($offer['description']),
            'from_date' => $offer['dateFrom'],
            'to_date' => $offer['dateTo'],
            'uses_per_customer' => 1,
            'uses_per_coupon' => $uses_per_coupon,
            'is_active' => '1',
            'stop_rules_processing' => '0',
            'is_advanced' => '1',
            'sort_order' => '0',
            'simple_action' => $offer['offerValueType'] == OfferApiService::TYPE_PERCENT ? Rule::BY_PERCENT_ACTION : Rule::CART_FIXED_ACTION,
            'discount_amount' => $offer['offerValueType'] == OfferApiService::TYPE_FREE_SHIPPING ? 0 : $offer['offerValue'],
            'discount_qty' => 0,
            'discount_step' => '0',
            'apply_to_shipping' => '0',
            'times_used' => '0',
            'is_rss' => '0',
            'coupon_type' => Rule::COUPON_TYPE_SPECIFIC,
            'use_auto_generation' => '0',
            'simple_free_shipping' => $offer['offerValueType'] == OfferApiService::TYPE_FREE_SHIPPING ? 1 : 0,
            'code' => $couponCode,
            'website_ids' => $this->getWebsiteIds(),
            'customer_group_ids' => $this->getCustomerGroupIds(),
            'coupon_code' => $couponCode,
            'from_wlc' => 1,
            'store_labels' => [
                0 => ''
            ]
        ]);

        if (isset($offer['excludeSku'])) {
            $allExcludeSku = explode(',', $offer['excludeSku']);
            $conditions = [
                'type' => \Magento\SalesRule\Model\Rule\Condition\Product\Combine::class,
                'attribute' => null,
                'operator' => null,
                'value' => '1',
                'is_value_processed' => null,
                'aggregator' => 'all',
                'conditions' => [],
            ];

            foreach ($allExcludeSku as $excludeSku) {
                $conditions['conditions'][] =
                    [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Product::class,
                        'attribute' => 'sku',
                        'operator' => '!=',
                        'value' => $excludeSku,
                        'is_value_processed' => false,
                        'attribute_scope' => ''
                    ];
            }

            $rule['actions_serialized'] = json_encode($conditions);
        }

        if (isset($offer['minimumAmountToBuy'])) {
            $conditions = array(
                'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
                'attribute' => null,
                'operator' => null,
                'value' => '1',
                'is_value_processed' => null,
                'aggregator' => 'all',
                'conditions' => [
                    [
                        'type' => \Magento\SalesRule\Model\Rule\Condition\Address::class,
                        'attribute' => 'base_subtotal',
                        'operator' => '>=',
                        'value' => $offer['minimumAmountToBuy'],
                        'is_value_processed' => false,
                    ],
                ],
            );

            $rule['conditions_serialized'] = json_encode($conditions);
        }

        try {
            $rule->save();
        } catch (\Exception $e) {

        }
    }

    /**
     * @return array
     */
    private function getWebsiteIds(): array
    {
        $websites = $this->storeManager->getWebsites();

        return array_map(function ($website) {
            return $website->getId();
        }, $websites);
    }

    /**
     * @return array
     */
    private function getCustomerGroupIds(): array
    {
        $collection = $this->customerGroupCollectionFactory->create();

        return array_map(function ($group) {
            return $group['value'];
        }, $collection->toOptionArray());
    }
}
