<?php


namespace Snowdog\ShippingLatency\Setup;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Snowdog\ShippingLatency\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;
use Magento\Cms\Model\BlockFactory as Block;

class UpgradeData implements UpgradeDataInterface
{
    // keys are setted for retrocompatibility, the _ is needed for config dynamic row options
    const DEFAULT_LATENCY_OPTIONS = [
                '_1' => [
                    'title' => 'More On The Way',
                    'cms_block' => 'more_on_the_way',
                    'button_class' => 'more-on-the-way',
                    'popup_id' => 'more-on-the-way-confirm'
                ],
                '_3' => [
                    'title' => 'Back Ordered',
                    'cms_block' => 'back_ordered',
                    'button_class' => 'back-ordered',
                    'popup_id' => 'back-ordered-confirm'
                ],
                '_7' => [
                    'title' => 'Custom Order',
                    'cms_block' => 'custom_order',
                    'button_class' => 'custom-order',
                    'popup_id' => 'custom-order-confirm'
                ],
                '_8' => [
                    'title' => 'Expanded Lead Time',
                    'cms_block' => 'expanded_lead_time',
                    'button_class' => 'expanded-lead-time-confirm',
                    'popup_id' => 'expanded-lead-time-confirm'
                ],
                '_9' => [
                    'title' => 'Sold Out',
                    'cms_block' => 'sold_out',
                    'button_class' => 'sold-out',
                    'popup_id' => 'sold-out'
                ]
            ];

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ConfigInterface
     */
    private $configInterface;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var Block
     */
    private $blockFactory;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ConfigInterface $configInterface,
        Json $jsonSerializer,
        Block $block
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->configInterface = $configInterface;
        $this->jsonSerializer = $jsonSerializer;
        $this->block = $block;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->addShippingLatencyAttribute($setup);
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->addDisplayInStockFrontendAttribute($setup);
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->disableAttributeOptionConfiguration($setup);
            $this->addDefaultLatencyOptions();
        }

        $setup->endSetup();
    }

    /**
     * @param EavSetup $eavSetup
     * @param string $attributeCode
     */
    protected function updateShippingLatencyAttribute(EavSetup $eavSetup, string $attributeCode)
    {
        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode,
            [
                'group' => 'General',
                'type' => 'varchar',
                'label' => 'Shipping Latency',
                'input' => 'select',
                'source' => 'Snowdog\ShippingLatency\Model\Config\Source\Options',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '0',
                'visible_on_front' => true,
                'used_in_product_listing' => true,
            ]
        );
    }

    private function addShippingLatencyAttribute(ModuleDataSetupInterface $setup)
    {
        $attributeCode = 'shipping_latency';
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $attributeBackendType = $eavSetup->getAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode,
            'backend_type'
        );

        if (isset($attributeBackendType) && $attributeBackendType === 'varchar') {
            return;
        }

        $this->updateShippingLatencyAttribute($eavSetup, $attributeCode);
    }

    private function addDisplayInStockFrontendAttribute(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'display_instock_frontend',
            [
                'group' => 'General',
                'type' => 'varchar',
                'label' => 'Display In Stock In Frontend',
                'input' => 'select',
                'source' => Boolean::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '1',
                'visible_on_front' => true,
                'used_in_product_listing' => true,
            ]
        );
    }

    private function disableAttributeOptionConfiguration(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->updateAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'shipping_latency',
            'is_user_defined',
            false
        );
    }

    private function addDefaultLatencyOptions(): void
    {
        $options = self::DEFAULT_LATENCY_OPTIONS;
        array_walk_recursive($options, function (&$value, $key) {
            if ($key == 'cms_block') {
                $blockModel = $this->block->create();
                $block = $blockModel->load($value, 'identifier');
                if ($block->getId()) {
                    $value = $block->getId();
                }
            }
        });
        $serializedData = $this->jsonSerializer->serialize($options);
        $this->configInterface->saveConfig(
            Data::XML_PATH_SHIPPING_LATENCY_CONFIGURATION,
            $serializedData,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            Store::DEFAULT_STORE_ID
        );
    }
}
