<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service;

interface ImageDownloaderInterface
{
    public function downloadImage(string $filePath, string $destinationPath, string $fileName) : bool;
}
