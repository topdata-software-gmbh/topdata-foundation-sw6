<?php

namespace Topdata\TopdataFoundationSW6\DataStructure;


/**
 * A simple collection of unique string values.
 * Optimized for handling binary UUIDs and other string data.
 *
 * 05/2025 created
 */
final class StringSet implements \Countable, \IteratorAggregate
{
    /**
     * @var array Internal associative array to store values.
     * The string values themselves are used as keys for uniqueness.
     */
    private $items = [];

    /**
     * Creates a new string set from the provided values.
     *
     * @param iterable $values String values to include in the set
     */
    public function __construct(iterable $values = [])
    {
        foreach ($values as $value) {
            $this->add($value);
        }
    }

    /**
     * Adds one or more string values to the set.
     *
     * @param string ...$values
     * @return self For method chaining
     */
    public function add(string ...$values): self
    {
        foreach ($values as $value) {
            $this->items[$value] = true;
        }
        return $this;
    }

    /**
     * Removes one or more values from the set.
     *
     * @param string ...$values
     * @return self For method chaining
     */
    public function remove(string ...$values): self
    {
        foreach ($values as $value) {
            unset($this->items[$value]);
        }
        return $this;
    }

    /**
     * Checks if the set contains the given value(s).
     *
     * @param string ...$values
     * @return bool True if all values are in the set
     */
    public function contains(string ...$values): bool
    {
        if (empty($values)) {
            return false;
        }
        
        foreach ($values as $value) {
            if (!isset($this->items[$value])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Clears all elements from the set.
     *
     * @return self For method chaining
     */
    public function clear(): self
    {
        $this->items = [];
        return $this;
    }

    /**
     * Creates a new set containing values from this set that aren't in another set.
     *
     * @param StringSet $other
     * @return StringSet
     */
    public function diff(StringSet $other): self
    {
        return new self(array_keys(array_diff_key($this->items, $other->items)));
    }

    /**
     * Creates a new set containing values common to both sets.
     *
     * @param StringSet $other
     * @return StringSet
     */
    public function intersect(StringSet $other): self
    {
        return new self(array_keys(array_intersect_key($this->items, $other->items)));
    }

    /**
     * Creates a new set containing values from both sets.
     *
     * @param StringSet $other
     * @return StringSet
     */
    public function union(StringSet $other): self
    {
        $result = $this->copy();
        foreach (array_keys($other->items) as $value) {
            $result->add($value);
        }
        return $result;
    }

    /**
     * Returns a copy of this set.
     *
     * @return StringSet
     */
    public function copy(): self
    {
        return new self($this->values());
    }

    /**
     * Returns the number of elements in the set.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Checks if the set is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Returns an array of all values in the set.
     *
     * @return array
     */
    public function values(): array
    {
        return array_keys($this->items);
    }

    /**
     * Returns an iterator for the set's values.
     *
     * @return \Traversable
     */
    public function getIterator(): \Traversable
    {
        yield from array_keys($this->items);
    }
}