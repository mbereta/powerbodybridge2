<?php
declare(strict_types=1);

namespace Powerbody\Bridge\Api;

interface ClientInterface
{
    public function call(string $method, array $params = null) : array;

    public function checkResponseIsValid(array $response) : bool;
}
