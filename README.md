# VertigoLabs Data Aware

DataAware provides a unique and consistent method of dynamic data handling.

## Dependencies

- Symfony's PropertyAccess component is used. You may install it via Composer using `composer require symfony/property-access`.

## Features

- Retrieve a specific data item or the full data set.
- Set and merge data into the class.
- Check for the existence of a data item.
- Apply default values to missing data.
- Normalize data keys into camelCase format.

## Methods

### setData($data, $normalize = true, $applyDefaults = true)

Set the data for the class. This method accepts three parameters:

__Parameters:__

`$data`: The data to be managed by the class. It is expected to be an array.

`$normalize` (optional): This is a boolean parameter that indicates whether the keys in the `$data` array should be
normalized into camelCase format. The default value is `true`, which means the keys will be normalized by default unless
specified otherwise.

`$applyDefaults` (optional): This is also a boolean parameter. If set to `true`, the method will apply default values
for any keys in the `$data` array that aren't already set. The default value of this parameter is `true`.

This method sets the data for the class while considering the provided normalization and defaulting parameters.

### mergeData($data, $normalize = true, $applyDefaults = true)

This method merges new data into the existing data set.

__Parameters:__

`$data`: The new data to merge into the existing data set. It should be an array.

`$normalize` (optional): This is a boolean parameter that dictates whether the keys in the `$data` array should be
normalized into camelCase format. The default value is `true`, which means the keys will be normalized by default unless
specified otherwise.

`$applyDefaults` (optional): This is also a boolean parameter. If set to `true`, the method will apply default values
for any keys in the `$data` array that aren't already set. The default value of this parameter is also `true`.

Merges new data into the existing data set. This method accepts the same parameters as `setData()`.

### getData($key = null, $default = null)

Retrieve specific, all, or a subset of processed data. This method accepts one optional parameter:

__Parameters:__

`$key` (optional): This is a parameter that accepts the key for the specific piece of data you want to retrieve. If this
parameter is not provided or null, the method will retrieve all the data.

`$default` (optional): This is a parameter that allows you to provide a default value, which will be returned if the
specified key does not exist in the data. This parameter defaults to `null`.

### getRawData($key = null, $default = null)

Retrieve specific, all, or a subset of raw (unprocessed) data. This method accepts two optional parameters:

__Parameters:__

`$key` (optional): This is a parameter that accepts the key for the specific piece of data you want to retrieve from the
raw data set. If this parameter is not provided or null, the method will retrieve all the raw data.

`$default` (optional): This is a parameter that allows you to provide a default value, which will be returned if the
specified key does not exist in the raw data. This parameter defaults to `null`.


### hasData($key)

Check for the existence of a data item in the processed data set. This method requires one parameter:

__Parameter:__

`$key`: This is the key of the specific data item you want to check. It should be a string value. If the key exists in
the processed data set, the method returns `true`. Otherwise, it returns `false`.


### hasRawData($key)

This method checks for the existence of a data item in the raw data set. It requires one parameter:

__Parameter:__

`$key`: This is a string value representing the key of the specific data item you want to verify the existence of in the
raw data set. If the key exists in the raw data set, the method returns `true`. Otherwise, it returns `false`.

## Exceptions

Throws a `DataNotFoundNoDefaultException` when trying to access a non-existent key and no default is provided.

## How to use

Here's a simple example of how to use this trait in your class:

```php
use VertigoLabs\DataAware\DataAwareTrait;
use VertigoLabs\DataAware\DataAwareInterface;

class EmployeeProfile implements DataAwareInterface
{
use DataAwareTrait;

    public function __construct(array $data)
    {
        $defaults = [
            'firstName' => 'Not provided',
            'lastName' => 'Not provided',
            'email' => 'Not provided'
        ];

        // Merging incoming data with defaults
        $data = array_merge($defaults, $data);

        $this->setData($data);
    }

    public function displayProfile()
    {
        foreach ($this->getData() as $key => $value) {
            echo ucfirst($key) . ": " . $value . "\n";
        }
    }

}

// Instantiate an EmployeeProfile with some initial data
$employee = new EmployeeProfile([
    'firstName' => 'John',
    'email' => 'john.doe@example.com'
]);

// Display the profile
$employee->displayProfile();

// Merge new data into the profile
$employee->mergeData([
    'lastName' => 'Doe',
    'position' => 'Software Developer',
    'address'=>[
        'street'=>'123 Main St',
        'city'=>'Anytown',
        'state'=>'NY','zip'=>'12345'
    ]
]);

// Display the updated profile
$employee->displayProfile();

// Retrieve a specific piece of data
echo "First Name: " . $employee->getData('firstName') . "\n";

// Check for the existence of a specific piece of data
echo "Does the profile have a position? " . ($employee->hasData('position') ? 'Yes' : 'No') . "\n";

// Use dot notation to retieve data from nested arrays
echo "City: " . $employee->getData('address.city') . "\n";

// Retrieve an an array of a subset of data
$name = $employee->getData(['firstName', 'lastName']);

// Display the subset of data
echo "Name: " . $name['firstName'] . " " . $name['lastName'] . "\n";

// Create an array of a subset of data with custom key names
$address = $employee->getData([
    'line1' => 'address.street',
    'locality' => 'address.city',
    'region' => 'address.state',
    'postalCode' => 'address.zip'
]);

// Display the custom subset of data
echo "Address: " . $address['line1'] . "\n";
echo "City: " . $address['locality'] . "\n";
echo "State: " . $address['region'] . "\n";
echo "Zip: " . $address['postalCode'] . "\n";
```
