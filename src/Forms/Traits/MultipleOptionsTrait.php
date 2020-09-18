<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Traits;

use Pupilsight\Contracts\Database\Connection;

/**
 * MultipleOptions
 *
 * Adds functionaly for types of input that offer users multiple options. Methods are provided for reading options from a variety of sources.
 *
 * @version v14
 * @since   v14
 */
trait MultipleOptionsTrait
{
    protected $options = array();

    /**
     * Build an internal options array from a provided CSV string.
     * @param   string  $value
     * @return  self
     */
    public function fromString($value)
    {
        if (empty($values)) {
            $values = '';
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf('Element %s: fromString expects value to be a string, %s given.', $this->getName(), gettype($value)));
        }

        if (!empty($value)) {
            $pieces = str_getcsv($value);

            foreach ($pieces as $piece) {
                $piece = trim($piece);
                $this->options[$piece] = $piece;
            }
        }

        return $this;
    }

    /**
     * Build an internal options array from a provided array of $key => $value pairs.
     * @param   array  $values
     * @return  self
     */
    public function fromArray($values)
    {
        if (empty($values)) {
            $values = [];
        }

        if (!is_array($values)) {
            throw new \InvalidArgumentException(sprintf('Element %s: fromArray expects value to be an Array, %s given.', $this->getName(), gettype($values)));
        }

        if (array_values($values) === $values) {
            // Convert non-associative array and trim values
            foreach ($values as $value) {
                $this->options[trim(strval($value))] = (!is_array($value))? trim($value) : $value;
            }
        } else {
            // Trim keys and values for associative array
            foreach ($values as $key => $value) {
                $this->options[trim($key)] = (!is_array($value))? trim($value) : $value;
            }
        }

        return $this;
    }

    /**
     * Build an internal options array from an SQL query with required value and name fields
     * @param   Connection  $pdo
     * @param   string      $sql
     * @param   array      $data
     * @return  self
     */
    public function fromQuery(Connection $pdo, $sql, $data = array(), $groupBy = false)
    {
        $results = $pdo->executeQuery($data, $sql);

        return $this->fromResults($results, $groupBy);
    }

    /**
     * Build an internal options array from the result set of a PDO query.
     * @param   object  $results
     * @return  string
     */
    public function fromResults($results, $groupBy = false)
    {
        if (empty($results) || !is_object($results)) {
            throw new \InvalidArgumentException(sprintf('Element %s: fromQuery expects value to be an Object, %s given.', $this->getName(), gettype($results)));
        }

        if ($results && $results->rowCount() > 0) {
            $options = array_filter($results->fetchAll(), function ($item) {
                return isset($item['value']) && isset($item['name']);
            });

            foreach ($options as $option) {
                $option = array_map('trim', $option);

                if ($groupBy !== false) {
                    $this->options[$option[$groupBy]][$option['value']] = __($option['name']);
                } else {
                    $this->options[$option['value']] = __($option['name']);
                }
            }
        }

        return $this;
    }

    /**
     * Gets the internal options collection.
     * @return  array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Recursivly count the total options in the collection.
     * @return  int
     */
    public function getOptionCount()
    {
        return count($this->options, COUNT_RECURSIVE);
    }
}
