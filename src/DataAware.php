<?php
declare(strict_types=1);

namespace VertigoLabs\DataAware;

use InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use VertigoLabs\DataAware\Exceptions\DataNotFoundNoDefaultException;

trait DataAware
{
    /**
     * @var array An unprocessed array of input data
     */
    private array $rawData = [];
    /**
     * @var array The processed array of input data
     */
    private array $data = [];

    /**
     * Retrieve a data item.
     *
     * This first parameter can be a string or an array.
     * If a string is specified, it will be used as the key
     * of the data element. Dot notation can be used to retrieve
     * data from multidimensional data items. If an array is used
     * each value in the array will be used as the key of a data
     * element and an array will be returned with the original input as keys
     *
     * Not specifying a key will return the full data array.
     *
     * The second parameter "default" can be used to return
     * a default value when the requested key is not found. When
     * an array is used for parameter one, an array of defaults, with
     * matching keys can also be used. If a single default is specified
     * when an array is used for the first parameter, that default will be
     * used for all non-existent keys. If no default is specified when using
     * and array as the first parameter, the resulting array element will
     * be empty.
     *
     * @param string|array|null $key The key of the data to retrieve
     * @param mixed|null $default The default value to use if the request key does not exist
     *
     * @return mixed The requested data
     * @throws DataNotFoundNoDefaultException
     */
    public function getData(string|array $key = null, mixed $default = null): mixed
    {
        if (null === $key && null === $default) {
            return $this->data;
        }

        if (is_array($key)) {
            $outputData = [];
            foreach ($key as $outputKey => $dataKey) {
                $outputDefault = is_array($default) && array_key_exists($outputKey, $default) ? $default[$outputKey] : $default;
                $outputData[$outputKey] = $this->getData($dataKey, $outputDefault);
            }
            return $outputData;
        }

        if ($this->hasData($key)) {
            return $this->dataAccessor($key);
        }

        if (func_num_args() === 1) {
            throw new DataNotFoundNoDefaultException($key, static::class);
        }

        return $default;
    }

    /**
     * Retrieves an item from the raw data input.
     * See getData description.
     *
     * @param string|array|null $key The key of the data to retrieve
     * @param mixed|null $default The default value to use if the request key does not exist
     *
     * @return mixed The requested data
     * @throws DataNotFoundNoDefaultException
     */
    public function getRawData(string|array $key = null, mixed $default = null): mixed
    {
        if (null === $key && null === $default) {
            return $this->rawData;
        }

        if (is_array($key)) {
            $outputData = [];
            foreach ($key as $outputKey => $dataKey) {
                $outputDefault = is_array($default) && array_key_exists($outputKey, $default) ? $default[$outputKey] : $default;
                $outputData[$outputKey] = $this->getRawData($dataKey, $outputDefault);
            }
            return $outputData;
        }

        if ($this->hasRawData($key)) {
            return $this->dataAccessor($key, $this->rawData);
        }

        if (func_num_args() === 1) {
            throw new DataNotFoundNoDefaultException($key, static::class);
        }

        return $default;
    }

    /**
     * Sets the data for the class.
     * The optional second parameter, normalizeDataKeys,
     * allows for all data keys to be normalized into camelCase.
     * The optional third parameter allows for control
     * of applying default values.
     *
     * @param array $data An array of input data
     * @param bool $normalizeKeys Flag used to control data key normalization (all keys will be camelCased)
     * @param bool $applyDefaults Flag used to control applying defaults to missing input data
     * @return DataAwareInterface
     */
    public function setData(array $data, bool $normalizeKeys = true, bool $applyDefaults = true): DataAwareInterface
    {
        $this->rawData = $data;
        $this->data = $this->processData($data, $normalizeKeys, $applyDefaults);
        return $this;
    }

    /**
     * Merges the data with the already existing data for the class.
     * The optional second parameter, normalizeDataKeys, allows for
     * all data keys to be normalized into camelCase
     *
     * @param array $data An array of input data to merge
     * @param bool $normalizeKeys Flag used to control data key normalization (all keys will be camelCased)
     * @param bool $applyDefaults Flag used to control applying defaults to missing input data
     * @return DataAwareInterface
     */
    public function mergeData(array $data, bool $normalizeKeys = true, bool $applyDefaults = true): DataAwareInterface
    {
        $this->rawData = array_merge($this->rawData, $data);
        $data = $this->processData($data, $normalizeKeys, $applyDefaults);
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Check for the existence of a data item
     * @param string|array $key The key of the data item to search for
     * @return bool
     */
    public function hasData(string|array $key): bool
    {
        try {
            $this->dataAccessor($key);
            return true;
        }catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Check for the existence of a raw data item
     * @param string|array $key The key of the raw data item to search for
     * @return bool
     */
    public function hasRawData(string|array $key): bool
    {
        try {
            $this->dataAccessor($key, $this->rawData);
            return true;
        }catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Returns the input data with normalization and defaults applied
     * @param array $data The data to process
     * @param bool $normalizeKeys Flag used to control data key normalization (all keys will be camelCased)
     * @param bool $applyDefaults Flag used to control applying defaults to missing input data
     * @return array The processed data
     */
    protected function processData(array $data, bool $normalizeKeys, bool $applyDefaults): array
    {
        if ($applyDefaults === true) {
            $defaultData = $this->defineDataDefaults();
            if (!empty($defaultData)) {
                $data = $this->applyDataDefaults($data, $defaultData);
            }
        }

        if ($normalizeKeys === true) {
            $data = $this->normalizeDataKeys($data);
        }

        return $data;
    }

    /**
     * Returns the input data array with keys normalized into camelCase
     * @param mixed $data The input data
     * @return mixed The normalized input data
     */
    protected function normalizeDataKeys(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        $result = [];
        foreach ($data as $key=>$value) {
            if(is_string($key)) {
                $key = $this->normalizeString($key);
            }
            $result[$key] = $this->normalizeDataKeys($value);
        }
        return $result;
    }

    /**
     * Convert a string into camelCase
     * @param string $string The string to convert
     * @return string The converted string
     */
    protected function normalizeString(string $string): string
    {
        $groupFirstSet = ctype_upper(substr($string, 0, 2));
        // camelize key
        $string = str_replace([' ', '_', '-', '\\', '/','.'], '', ucwords($string, ' _-/\\.'));

        if (!$groupFirstSet || (!ctype_upper($string) && !ctype_upper(substr($string, 1, 1)))) {
            $string = lcfirst($string);
        }
        return $string;
    }

    /**
     * Returns an array of default values.
     * Intended to be overridden
     *
     * @return array An associative array of default values
     */
    protected function defineDataDefaults(): array
    {
        return [];
    }

    /**
     * Recursively apply default values to an array of input
     * data based on key. NULL and missing values in the input array
     * are replaced by the values in the defaults array.
     *
     * @param array $data The input data
     * @param array $defaults The default values
     * @return array The input data with default values applied
     */
    protected function applyDataDefaults(array $data, array $defaults): array
    {
        $this->flattenArray($defaults);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        foreach ($defaults as $key => $defaultValue) {
            $inputValue = $propertyAccessor->getValue($data, $key);
            if (empty($inputValue) && !empty($defaults)) {
                $propertyAccessor->setValue($data, $key, $defaultValue);
            }
        }
        return $data;
    }

    /**
     * Provides access to a data item through a key
     *
     * @param string $key The key of the data item to retrieve
     *
     * @param array|null $haystack
     * @return mixed The value of the data item
     */
    private function dataAccessor(string $key, ?array $haystack=null): mixed
    {
        if (null === $haystack) {
            $haystack = $this->data;
        }

        // simple array key access to values
        if (array_key_exists($key, $haystack)) {
            return $haystack[$key];
        }

        // dot notation access to values
        if (substr_count($key,'.')) {
            $value = array_reduce(explode('.', $key), static function ($values, $param) {

                if (is_array($values) && array_key_exists($param, $values)) {
                    return $values[$param];
                }
                return '___VALUENOTFOUND___';
            }, $haystack);

            if ($value !== '___VALUENOTFOUND___') {
                return $value;
            }
        }

        // property path access to values
        if (substr_count($key,'[')) {
            $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
                ->enableExceptionOnInvalidIndex()
                ->getPropertyAccessor();
            if ($propertyAccessor->isReadable($haystack, $key)) {
                return $propertyAccessor->getValue($haystack, $key);
            }
        }
        throw new InvalidArgumentException(sprintf('Data Key "%s" not found', $key));
    }

    /**
     * Flatten an array. The result is an array with property access propertyPaths as the keys
     *
     * @param array $elements The elements to flatten
     * @param array|null $subNode The sub-node to flatten
     * @param null $path The current path in the array hierarchy
     */
    final protected function flattenArray(array &$elements, array $subNode = null, $path = null): void
    {
        if (null === $subNode) {
            $subNode = &$elements;
        }

        foreach ($subNode as $key => $value) {
            if (is_array($value)) {
                $nodePath = $path ? $path.'['.$key.']' : '['.$key.']';
                $this->flattenArray($elements, $value, $nodePath);
                if (null === $path) {
                    unset($elements[$key]);
                }
            } elseif (null !== $path) {
                $elements[$path.'['.$key.']'] = $value;
            }else{
                unset($elements[$key]);
                $elements['['.$key.']'] = $value;
            }
        }
    }
}
