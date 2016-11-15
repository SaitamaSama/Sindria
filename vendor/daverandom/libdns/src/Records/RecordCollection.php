<?php
/**
 * Collection of Record objects
 *
 * PHP version 5.4
 *
 * @category LibDNS
 * @package Records
 * @author Chris Wright <https://github.com/DaveRandom>
 * @copyright Copyright (c) Chris Wright <https://github.com/DaveRandom>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @version 2.0.0
 */
namespace LibDNS\Records;

/**
 * Collection of Record objects
 *
 * @category LibDNS
 * @package Records
 * @author Chris Wright <https://github.com/DaveRandom>
 */
class RecordCollection implements \Iterator, \Countable
{
    /**
     * @var \LibDNS\Records\Record[] List of records held in the collection
     */
    private $records = [];

    /**
     * @var \LibDNS\Records\Record[][] Map of Records in the collection grouped by record name
     */
    private $nameMap = [];

    /**
     * @var int Number of Records in the collection
     */
    private $length = 0;

    /**
     * @var int Whether the collection holds question or resource records
     */
    private $type;

    /**
     * @var int Iteration pointer
     */
    private $position = 0;

    /**
     * Constructor
     *
     * @param int $type Can be indicated using the RecordTypes enum
     * @throws \InvalidArgumentException When the specified record type is invalid
     */
    public function __construct($type)
    {
        if ($type !== RecordTypes::QUESTION && $type !== RecordTypes::RESOURCE) {
            throw new \InvalidArgumentException('Record type must be QUESTION or RESOURCE');
        }

        $this->type = $type;
    }

    /**
     * Add a record to the correct bucket in the name map
     *
     * @param \LibDNS\Records\Record $record The record to add
     */
    private function addToNameMap(Record $record)
    {
        if (!isset($this->nameMap[$name = (string) $record->getName()])) {
            $this->nameMap[$name] = [];
        }

        $this->nameMap[$name][] = $record;
    }

    /**
     * Remove a record from the name map
     *
     * @param \LibDNS\Records\Record $record The record to remove
     */
    private function removeFromNameMap(Record $record)
    {
        if (!empty($this->nameMap[$name = (string) $record->getName()])) {
            foreach ($this->nameMap[$name] as $key => $item) {
                if ($item === $record) {
                    array_splice($this->nameMap[$name], $key, 1);
                    break;
                }
            }
        }

        if (empty($this->nameMap[$name])) {
            unset($this->nameMap[$name]);
        }
    }

    /**
     * Add a record to the collection
     *
     * @param \LibDNS\Records\Record $record The record to add
     * @throws \InvalidArgumentException When the wrong record type is supplied
     */
    public function add(Record $record)
    {
        if (($this->type === RecordTypes::QUESTION && !($record instanceof Question))
          || ($this->type === RecordTypes::RESOURCE && !($record instanceof Resource))) {
            throw new \InvalidArgumentException('Incorrect record type for this collection');
        }

        $this->records[] = $record;
        $this->addToNameMap($record);
        $this->length++;
    }

    /**
     * Remove a record from the collection
     *
     * @param \LibDNS\Records\Record $record The record to remove
     */
    public function remove(Record $record)
    {
        foreach ($this->records as $key => $item) {
            if ($item === $record) {
                array_splice($this->records, $key, 1);
                $this->removeFromNameMap($record);
                $this->length--;
                return;
            }
        }

        throw new \InvalidArgumentException('The supplied record is not a member of this collection');
    }

    /**
     * Test whether the collection contains a specific record
     *
     * @param \LibDNS\Records\Record $record       The record to search for
     * @param bool $sameInstance Whether to perform strict comparisons in search
     * @return bool
     */
    public function contains(Record $record, $sameInstance = false)
    {
        return in_array($record, $this->records, (bool) $sameInstance);
    }

    /**
     * Get all records in the collection that refer to the specified name
     *
     * @param string $name The name to match records against
     * @return \LibDNS\Records\Record[]
     */
    public function getRecordsByName($name)
    {
        return isset($this->nameMap[$name = strtolower($name)]) ? $this->nameMap[$name] : [];
    }

    /**
     * Get a record from the collection by index
     *
     * @param int $index Record index
     * @return \LibDNS\Records\Record
     * @throws \OutOfBoundsException When the supplied index does not refer to a valid record
     */
    public function getRecordByIndex($index)
    {
        if (isset($this->records[$index])) {
            return $this->records[$index];
        }

        throw new \OutOfBoundsException('The specified index ' . $index . ' does not exist in the collection');
    }

    /**
     * Remove all records in the collection that refer to the specified name
     *
     * @param string $name The name to match records against
     * @return int The number of records removed
     */
    public function clearRecordsByName($name)
    {
        $count = 0;

        if (isset($this->nameMap[$name = strtolower($name)])) {
            unset($this->nameMap[$name]);

            foreach ($this->records as $index => $record) {
                if ($record->getName() === $name) {
                    unset($this->records[$index]);
                    $count++;
                }
            }

            $this->records = array_values($this->records);
        }

        return $count;
    }

    /**
     * Remove all records from the collection
     */
    public function clear()
    {
        $this->records = $this->nameMap = [];
        $this->length = $this->position = 0;
    }

    /**
     * Get a list of all names referenced by records in the collection
     *
     * @return string[]
     */
    public function getNames()
    {
        return array_keys($this->nameMap);
    }

    /**
     * Get whether the collection holds question or resource records
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the record indicated by the iteration pointer (Iterator interface)
     *
     * @return \LibDNS\Records\Record
     * @throws \OutOfBoundsException When the pointer does not refer to a valid record
     */
    public function current()
    {
        if (!isset($this->records[$this->position])) {
            throw new \OutOfBoundsException('The current pointer position is invalid');
        }

        return $this->records[$this->position];
    }

    /**
     * Get the value of the iteration pointer (Iterator interface)
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Increment the iteration pointer (Iterator interface)
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Reset the iteration pointer to the beginning (Iterator interface)
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Test whether the iteration pointer indicates a valid record (Iterator interface)
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->records[$this->position]);
    }

    /**
     * Get the number of records in the collection (Countable interface)
     *
     * @return int
     */
    public function count()
    {
        return $this->length;
    }
}
