<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Block\Adminhtml\Import\Category;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Powerbody\Bridge\Model\Imported\Category;

class RendererFactory implements RendererFactoryInterface
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    public function create(Category $importedCategory) : BlockInterface
    {
        $block = $this->layout->createBlock(Renderer::CLASS);
        $block->setData('imported_category', $importedCategory);

        return $block;
    }
}
