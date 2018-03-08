<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Block\Adminhtml\Import;

use Magento\Backend\Block\Widget\Container;

class Index extends Container
{
    protected function _prepareLayout() : self
    {
        $this->buttonList->add('save', [
            'id' => 'save',
            'label' => __('Save'),
            'class' => 'action-primary',
            'onclick' => 'document.querySelectorAll("#container form")[0].submit()',
            'class_name' => 'Magento\Backend\Block\Widget\Button',
        ]);

        $this->buttonList->add('select_deselect', [
            'id' => 'select_deselect',
            'label' => __('Select all'),
            'class' => 'action',
            'class_name' => 'Magento\Backend\Block\Widget\Button',
            'data_attribute' => [
                'type' => 'select',
            ],
        ]);

        return parent::_prepareLayout();
    }
}
