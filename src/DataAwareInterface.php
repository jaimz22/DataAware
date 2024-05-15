<?php
namespace VertigoLabs\DataAware;

use VertigoLabs\DataAware\Exceptions\DataNotFoundNoDefaultException;

interface DataAwareInterface
{
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
    public function getData(string|array $key = null, mixed $default = null): mixed;

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
    public function getRawData(string|array $key = null, mixed $default = null): mixed;

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
    public function setData(array $data, bool $normalizeKeys = true, bool $applyDefaults = true): DataAwareInterface;

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
    public function mergeData(array $data, bool $normalizeKeys = true, bool $applyDefaults = true): DataAwareInterface;

    /**
     * Check for the existence of a data item
     * @param string|array $key The key of the data item to search for
     * @return bool
     */
    public function hasData(string|array $key): bool;

    /**
     * Check for the existence of a raw data item
     * @param string|array $key The key of the raw data item to search for
     * @return bool
     */
    public function hasRawData(string|array $key): bool;
}

