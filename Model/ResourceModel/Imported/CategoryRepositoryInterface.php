<?php

namespace Powerbody\Bridge\Model\ResourceModel\Imported;

interface CategoryRepositoryInterface
{
    /**
     * @param array $manufacturerDataArray
     */
    public function addOrUpdateIfExist(array $manufacturerDataArray);

    /**
     * @param array $baseIdsArray
     */
    public function deleteAllWithNotMatchingBaseId(array $baseIdsArray);
}
