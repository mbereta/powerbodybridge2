<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Entity\Category;

interface RepositoryInterface
{
    public function findAll() : array;

    public function getSelectedCategoryData(array $selectedCategoryIdsArray) : array;
}
