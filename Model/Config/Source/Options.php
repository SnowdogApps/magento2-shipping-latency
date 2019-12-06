<?php

namespace Snowdog\ShippingLatency\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;
use Magento\Framework\DB\Ddl\Table;

class Options extends AbstractSource
{
    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    public function __construct(
        AttributeFactory $attributeFactory
    )
    {
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            [
                'label' => __('None'),
                'value' => ''
            ],
            [
                'label' => __('More On The Way'),
                'value' => '1'
            ],
            [
                'label' => __('Back Ordered'),
                'value' => '3'
            ],
            [
                'label' => __('Custom Order'),
                'value' => '7'
            ],
            [
                'label' => __('Expanded Lead Time'),
                'value' => '8'
            ],
            [
                'label' => __('Sold Out'),
                'value' => '9'
            ]
        ];

        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_SMALLINT,
                'nullable' => true,
                'comment' => 'Shipping Latency',
            ],
        ];
    }

    /**
     * @param int $store
     * @return \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return $this->attributeFactory->create()->getFlatUpdateSelect($this->getAttribute(), $store);
    }
}
