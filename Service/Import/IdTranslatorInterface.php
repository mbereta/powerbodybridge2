<?php

namespace Powerbody\Bridge\Service\Import;

interface IdTranslatorInterface
{
    public function translateBaseToClientIds(array $idsArray) : array;
}
