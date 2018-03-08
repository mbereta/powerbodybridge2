<?php

namespace Powerbody\Bridge\Service\Imported;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class ImportedCategory implements ImportedEntityServiceInterface
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

    public function setAsSelectedOnlyIds(array $categoryIds)
    {
        if (empty($categoryIds)) {
            $this->setAllAsNotSelected();
            return;
        }
        $this->setAsSelectedIfIdInArray($categoryIds);
        $this->setAsNotSelectedIfIdNotInArray($categoryIds);
    }

    private function setAllAsNotSelected()
    {
        $this
            ->dbConnection
            ->update(
                $this->resourceConnection->getTableName('bridge_imported_category'),
                ['is_selected' => false]
            );
    }

    private function setAsSelectedIfIdInArray(array $categoryIds)
    {
        $idsImplodedString = implode(',', $categoryIds);
        $this->dbConnection
            ->update(
                $this->resourceConnection->getTableName('bridge_imported_category'),
                ['is_selected' => true],
                "id in ($idsImplodedString)"
            );
    }

    private function setAsNotSelectedIfIdNotInArray(array $categoryIds)
    {
        $idsImplodedString = implode(',', $categoryIds);
        $this->dbConnection
            ->update(
                $this->resourceConnection->getTableName('bridge_imported_category'),
                ['is_selected' => false],
                "id not in ($idsImplodedString)"
            );
    }
}
