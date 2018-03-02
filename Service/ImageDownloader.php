<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service;

class ImageDownloader implements ImageDownloaderInterface
{
    public function downloadImage(string $filePath, string $destinationPath) : bool
    {
        if (false === $this->checkIfImageExists($filePath) || false === copy($filePath, $destinationPath)) {
            throw new ImageFileNotFoundException('');
        }

        return true;
    }

    private function checkIfImageExists(string $fileUrl) : bool
    {
        $curl = curl_init($fileUrl);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_exec($curl);
        $returnCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return $returnCode === 200;
    }
}
