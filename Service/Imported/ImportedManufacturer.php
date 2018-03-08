<?php

namespace Powerbody\Bridge\Service\Imported;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class ImportedManufacturer implements ImportedEntityServiceInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $dbConnection;

    public function __construct(ResourceConnection $connection)
    {
        $this->resourceConnection = $connection;
        $this->dbConnection = $connection->getConnection();
    }

    public function setAsSelectedOnlyIds(array $manufacturerIds)
    {
        if (empty($manufacturerIds)) {
            $this->setAllAsNotSelected();
            return;
        }
        $this->setAsSelectedIfIdInArray($manufacturerIds);
        $this->setAsNotSelectedIfIdNotInArray($manufacturerIds);
    }

    private function setAllAsNotSelected()
    {
        $this
            ->dbConnection
            ->update(
                $this->resourceConnection->getTableName('bridge_imported_manufacturer'),
                ['is_selected' => false]
            );
    }

    private function setAsSelectedIfIdInArray(array $manufacturerIds)
    {
        $idsImplodedString = implode(',', $manufacturerIds);
        $this->dbConnection
            ->update(
                $this->resourceConnection->getTableName('bridge_imported_manufacturer'),
                ['is_selected' => true],
                "id in ($idsImplodedString)"
            );
    }

    private function setAsNotSelectedIfIdNotInArray(array $manufacturerIds)
    {
        $idsImplodedString = implode(',', $manufacturerIds);
        $this->dbConnection
            ->update(
                $this->resourceConnection->getTableName('bridge_imported_manufacturer'),
                ['is_selected' => false],
                "id not in ($idsImplodedString)"
            );
    }
}
