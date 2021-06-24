<?php
namespace VertigoLabs\DataAware;

interface DataAwareInterface
{
    public function getData(string|array $key = null, mixed $default = null): mixed;
    public function getRawData(string|array $key = null, mixed $default = null): mixed;
    public function setData(array $data, bool $normalizeKeys = true, bool $applyDefaults = true): DataAwareInterface;
    public function mergeData(array $data, bool $normalizeKeys = true, bool $applyDefaults = true): DataAwareInterface;
    public function hasData(string|array $key): bool;
    public function hasRawData(string|array $key): bool;

}

