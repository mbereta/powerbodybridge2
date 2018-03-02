<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Block\Adminhtml\Import\Category;

use Magento\Framework\View\Element\BlockInterface;
use Powerbody\Bridge\Model\Imported\Category;

interface RendererFactoryInterface
{
    public function create(Category $category) : BlockInterface;
}
