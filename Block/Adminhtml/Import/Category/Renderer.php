<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Block\Adminhtml\Import\Category;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Powerbody\Bridge\Model\Imported\Category;

class Renderer extends Template
{
    /**
     * @var RendererFactoryInterface
     */
    private $renderFactory;

    public function __construct(
        Context $context,
        RendererFactoryInterface $rendererFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->renderFactory = $rendererFactory;
        $this->setTemplate('import/category/renderer.phtml');
    }

    public function renderNew(Category $importedCategory) : string
    {
        return $this->renderFactory->create($importedCategory)->toHtml();
    }
}
