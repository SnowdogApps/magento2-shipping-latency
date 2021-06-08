<?php

namespace Snowdog\ShippingLatency\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\ResourceModel\Entity\AttributeFactory;
use Magento\Framework\DB\Ddl\Table;
use Snowdog\ShippingLatency\Helper\Data;

class Options extends AbstractSource
{
    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * @var Data
     */
    private Data $helper;

    public function __construct(
        AttributeFactory $attributeFactory,
        Data $helper
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->helper = $helper;
    }

    public function getAllOptions(): array
    {
        return $this->helper->getLatencyData();
    }

    /**
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
