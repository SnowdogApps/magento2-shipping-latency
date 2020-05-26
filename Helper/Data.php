<?php

namespace Snowdog\ShippingLatency\Helper;

use Magento\Catalog\Model\Product;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Data
 * @package Snowdog\ShippingLatency\Helper
 */
class Data extends AbstractHelper
{

    /**
     * @var array
     */
    public $latencyData = [];
    /**
     * @var BlockRepository
     */
    private $blockRepository;

    /**
     * Data constructor.
     * @param Context $context
     * @param BlockRepository $block
     */
    public function __construct(
        Context $context,
        BlockRepository $blockRepository
    ) {
        parent::__construct($context);
        $this->blockRepository = $blockRepository;
    }

    public function getLatencyData()
    {
        if (empty($this->latencyData)) {
            $this->latencyData = [
                1 => [
                    'title' => __('More On The Way'),
                    'btnClass' => 'more-on-the-way',
                    'popupHtml' => $this->getCmsBlockContent('more_on_the_way'),
                    'popupId' => 'more-on-the-way-confirm',
                ],
                3 => [
                    'title' => __('Back Ordered'),
                    'btnClass' => 'back-ordered',
                    'popupHtml' => $this->getCmsBlockContent('back_ordered'),
                    'popupId' => 'back-ordered-confirm',
                ],
                7 => [
                    'title' => __('Custom Order'),
                    'btnClass' => 'custom-order',
                    'popupHtml' => $this->getCmsBlockContent('custom_order'),
                    'popupId' => 'custom-order-confirm',
                ],
                8 => [
                    'title' => __('Expanded Lead Time'),
                    'btnClass' => 'expanded-lead-time-confirm',
                    'popupHtml' => $this->getCmsBlockContent('expanded_lead_time'),
                    'popupId' => 'expanded-lead-time-confirm'
                ],
                9 => [
                    'title' => __('Sold Out'),
                    'btnClass' => 'sold-out',
                    'popupHtml' => $this->getCmsBlockContent('sold_out'),
                    'popupId' => 'sold-out'
                ]
            ];
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

    private function getCmsBlockContent(string $blockId)
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


    /**
     * @param array $productData
     * @return mixed|null
     */
    private function getShippingLatencyId(array $productData)
    {
        return empty($productData['shipping_latency']) ? null : $productData['shipping_latency'];
    }
}
