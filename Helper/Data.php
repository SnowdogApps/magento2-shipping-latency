<?php

namespace Snowdog\ShippingLatency\Helper;

use Magento\Catalog\Model\Product;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Data
 * @package Snowdog\ShippingLatency\Helper
 */
class Data extends AbstractHelper
{
    const XML_PATH_SHIPPING_LATENCY_CONFIGURATION = 'shipping_latency/options/mapping';

    /**
     * @var array
     */
    public $latencyData = [];

    /**
     * @var BlockRepository
     */
    private $blockRepository;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * Data constructor.
     * @param Context $context
     * @param BlockRepository $blockRepository
     * @param Json $jsonSerializer
     */
    public function __construct(
        Context $context,
        BlockRepository $blockRepository,
        Json $jsonSerializer
    ) {
        parent::__construct($context);
        $this->blockRepository = $blockRepository;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @return array
     */
    public function getLatencyData()
    {
        if (empty($this->latencyData)) {
            $latencyDataConfiguration = $this->getShippingLatencyConfiguration();
            foreach ($latencyDataConfiguration as $key => $latencyDataOption) {
                $this->latencyData[trim($key, '_')] = [
                    'label' => __($latencyDataOption['title']),
                    'value' => trim($key, '_'),
                    'title' => __($latencyDataOption['title']),
                    'btnClass' => $latencyDataOption['button_class'] ?? $latencyDataOption['cms_block'],
                    'popupHtml' => $this->getCmsBlockContent($latencyDataOption['cms_block']),
                    'popupId' => $latencyDataOption['popup_id'] ?? $latencyDataOption['cms_block'],
                ];
            }
        }
        return $this->latencyData;
    }

    /**
     * @param array $productData
     * @return array|null
     */
    public function getProductLatencyData(array $productData)
    {
        $shippingLatencyId = $this->getShippingLatencyId($productData);
        $latencyData = $this->getLatencyData();
        if (!$shippingLatencyId || !isset($latencyData[$shippingLatencyId])) {
            return null;
        }
        return $latencyData[$shippingLatencyId];
    }

    public function isShippingLatencyAllowed($product): bool
    {
        /** @var Product $product */
        $isSalable = $product->getIsSalable();
        $isShownInStock = $product->getData('display_instock_frontend');
        // below the validation for products with no display_instock_frontend set
        if ($isShownInStock === null) {
            $isShownInStock = true;
        }

        if ($isShownInStock && !$isSalable) {
            $isShownInStock = false;
        }

        return (bool) $isShownInStock;
    }

    private function getCmsBlockContent(string $blockId): string
    {
        try {
            $cmsBlock = $this->blockRepository->getById($blockId);
        } catch (LocalizedException $exception) {
            $this->_logger->error('Shipping latency: Can\'t find ['. $blockId .'] CMS block');
            return 'Popup block error. Please check log file';
        }
        return $cmsBlock->getContent();
    }

    /**
     * @param array $productData
     * @return string
     */
    public function getProductPopupHtml(array $productData)
    {
        $shippingLatencyId = $this->getShippingLatencyId($productData);
        $latencyData = $this->getLatencyData();
        if (!$shippingLatencyId || !isset($latencyData[$shippingLatencyId]['popupHtml'])) {
            return '';
        }
        return $latencyData[$shippingLatencyId]['popupHtml'];
    }


    /**
     * @param array $productData
     * @return string
     */
    public function getTitle(array $productData)
    {
        $shippingLatencyId = $this->getShippingLatencyId($productData);
        $latencyData = $this->getLatencyData();
        if (!$shippingLatencyId || !isset($latencyData[$shippingLatencyId])) {
            return '';
        }
        return $latencyData[$shippingLatencyId]['title'];
    }

    public function getShippingLatencyConfiguration(
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        ): array {
            $value = $this->scopeConfig->getValue(self::XML_PATH_SHIPPING_LATENCY_CONFIGURATION, $scope);
            return !empty($value) ? $this->jsonSerializer->unserialize($value) : [];
    }

    /**
     * @param array $productData
     * @return mixed|null
     */
    private function getShippingLatencyId(array $productData)
    {
        return empty($productData['shipping_latency']) ? null : $productData['shipping_latency'];
    }
}
