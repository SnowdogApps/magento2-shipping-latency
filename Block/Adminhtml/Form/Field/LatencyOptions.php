<?php
/**
 * Class LatencyOptions
 * @copyright Copyright © 2021 Snowdog. All rights reserved.
 * @see https://snow.dog
 */
declare(strict_types=1);

namespace Snowdog\ShippingLatency\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Snowdog\ShippingLatency\Block\Adminhtml\Form\Field\CmsBlockColumn;

class LatencyOptions extends AbstractFieldArray
{
    /**
     * @var CmsBlockColumn
     */
    private $cmsRenderer;

    protected function _prepareToRender(): void
    {
        $this->addColumn(
            'title',
            [
                'label' => __('Option Title'),
                'class' => 'required-entry'
            ]
        );
        $this->addColumn(
            'cms_block',
            [
                'label' => __('CMS Block'),
                'renderer' => $this->getCmsBlockRenderer()
            ]
        );
        $this->addColumn(
            'button_class',
            [
                'label' => __('Button Class'),
            ]
        );
        $this->addColumn(
            'popup_id',
            [
                'label' => __('Popup Id'),
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $cmsBlock = $row->getCmsBlock();
        if ($cmsBlock !== null) {
            $options['option_' . $this->getCmsBlockRenderer()->calcOptionHash($cmsBlock)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|\Snowdog\ShippingLatency\Block\Adminhtml\Form\Field\CmsBlockColumn
     * @throws LocalizedException
     */
    private function getCmsBlockRenderer()
    {
        if (!$this->cmsRenderer) {
            $this->cmsRenderer = $this->getLayout()->createBlock(
                CmsBlockColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->cmsRenderer;
    }
}
