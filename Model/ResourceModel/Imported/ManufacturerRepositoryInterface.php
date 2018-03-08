<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Model\ResourceModel\Imported;

use Powerbody\Bridge\Model\Imported\Manufacturer;

interface ManufacturerRepositoryInterface
{
    public function addOrUpdateIfExist(array $manufacturerDataArray);

    public function deleteAllWithNotMatchingBaseId(array $manufacturerBaseIdsArray);

    public function getImportedManufacturerByBaseId($baseManufacturerId) : Manufacturer;
}
