<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Service;

class ImageDownloader implements ImageDownloaderInterface
{

    public function downloadImage(string $filePath, string $destinationPath, string $fileName) : bool
    {
        if (false === file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        if (false === $this->checkIfImageExists($filePath)) {
            return false;
        }

        return copy($filePath, $destinationPath . $fileName);
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
