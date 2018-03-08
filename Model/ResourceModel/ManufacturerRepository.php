<?php

namespace Powerbody\Bridge\Model\ResourceModel;

use Powerbody\Bridge\Model\Imported\Manufacturer;
use Powerbody\Bridge\Model\Imported\ManufacturerFactory as ImportedManufacturerFactory;
use Powerbody\Bridge\Model\ResourceModel\Imported\Manufacturer as ImportedManufacturerResourceModel;
use Powerbody\Bridge\Model\ResourceModel\Imported\Manufacturer\Collection as ImportedManufacturerCollection;
use Powerbody\Bridge\Service\ImageDownloaderInterface;
use Powerbody\Bridge\Service\ImageFileNotFoundException;
use Psr\Log\LoggerInterface;

class ManufacturerRepository implements ManufacturerRepositoryInterface
{

    const MANUFACTURER_LOGO_PATH = 'manufacturer/';

    /**
     * @var ImportedManufacturerCollection
     */
    private $manufacturerCollection;

    /**
     * @var ImportedManufacturerFactory
     */
    private $importedManufacturerFactory;

    /**
     * @var ImportedManufacturerResourceModel
     */
    private $importedManufacturerResourceModel;

    /**
     * @var ImageDownloaderInterface
     */
    private $imageDownloader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ImportedManufacturerCollection $manufacturerCollection,
        ImportedManufacturerFactory $importedManufacturerFactory,
        ImportedManufacturerResourceModel $importedManufacturerResourceModel,
        ImageDownloaderInterface $imageDownloader,
        LoggerInterface $logger
    ) {
        $this->manufacturerCollection = $manufacturerCollection;
        $this->importedManufacturerFactory = $importedManufacturerFactory;
        $this->importedManufacturerResourceModel = $importedManufacturerResourceModel;
        $this->imageDownloader = $imageDownloader;
        $this->logger = $logger;
    }

    /**
     * @param int $manufacturerBaseId
     *
     * @return Manufacturer
     */
    public function getImportedManufacturerModelByBaseId($manufacturerBaseId)
    {
        $importedManufacturerModel = $this->importedManufacturerFactory->create();
        $this->importedManufacturerResourceModel->load(
            $importedManufacturerModel,
            $manufacturerBaseId,
            'base_manufacturer_id'
        );

        return $importedManufacturerModel;
    }

    /**
     * @return string
     */
    public function getManufacturerDestinationUrl()
    {
        return BP . '/pub/media/';
    }

    /**
     * @return ImportedManufacturerCollection
     */
    public function getSelectedImportedManufacturerCollection()
    {
        $selectedImportedManufacturerCollection = $this->manufacturerCollection
            ->addFieldToFilter('is_selected', 1);

        return $selectedImportedManufacturerCollection;
    }

    /**
     * @param array $activeManufacturerIdsArray
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getNotSelectedImportedManufacturerCollection(array $activeManufacturerIdsArray)
    {
        $notSelectedImportedManufacturerCollection = $this->importedManufacturerFactory
            ->create()
            ->getCollection();

        if (false === empty($activeManufacturerIdsArray)) {
            $notSelectedImportedManufacturerCollection
                ->addFieldToFilter(['client_manufacturer_id', 'client_manufacturer_id'],
                    [
                        ['nin' => $activeManufacturerIdsArray],
                        ['null'  => true]
                    ]
                );
        }

        return $notSelectedImportedManufacturerCollection;
    }

    /**
     * @param array $manufacturerDataArray
     * @return array
     */
    public function downloadManufacturerLogo(array $manufacturerDataArray)
    {
        try {
            $logoUrl = $manufacturerDataArray['logo_url'];
            $mediaDir = $this->getManufacturerDestinationUrl();

            $destinationLogoPath = $mediaDir . self::MANUFACTURER_LOGO_PATH;
            $this->imageDownloader->downloadImage($logoUrl, $destinationLogoPath, (string) $manufacturerDataArray['logo']);
        } catch (ImageFileNotFoundException $e) {
            unset($manufacturerDataArray['logo']);
            $this->logger->debug(__('Could not find image for manufacturer') . ': '
                . $manufacturerDataArray['name']);
        } catch (\Exception $e) {
            unset($manufacturerDataArray['logo']);
            $this->logger->error($e);
        }

        return $manufacturerDataArray;
    }

}
