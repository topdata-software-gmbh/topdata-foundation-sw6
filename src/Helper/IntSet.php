<?php

namespace Topdata\TopdataFoundationSW6\Helper;

/**
 * A highly efficient collection of unique integer values.
 * Optimized for handling integer IDs with minimal overhead.
 *
 * 05/2025 created
 */
final class IntSet implements \Countable, \IteratorAggregate
{
    /**
     * @var array Internal associative array to store integers.
     * Integer values are used as keys for uniqueness.
     */
    private $items = [];

    /**
     * Creates a new integer set from the provided values.
     *
     * @param iterable $values Integer values to include in the set
     */
    public function __construct(iterable $values = [])
    {
        foreach ($values as $value) {
            $this->add($value);
        }
    }

    /**
     * Adds one or more integer values to the set.
     *
     * @param int ...$values
     * @return self For method chaining
     */
    public function add(int ...$values): self
    {
        foreach ($values as $value) {
            $this->items[$value] = true;
        }
        return $this;
    }

    /**
     * Removes one or more values from the set.
     *
     * @param int ...$values
     * @return self For method chaining
     */
    public function remove(int ...$values): self
    {
        foreach ($values as $value) {
            unset($this->items[$value]);
        }
        return $this;
    }

    /**
     * Checks if the set contains the given integer value(s).
     *
     * @param int ...$values
     * @return bool True if all values are in the set
     */
    public function contains(int ...$values): bool
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
     * @param IntSet $other
     * @return IntSet
     */
    public function diff(IntSet $other): self
    {
        return new self(array_keys(array_diff_key($this->items, $other->items)));
    }

    /**
     * Creates a new set containing values common to both sets.
     *
     * @param IntSet $other
     * @return IntSet
     */
    public function intersect(IntSet $other): self
    {
        return new self(array_keys(array_intersect_key($this->items, $other->items)));
    }

    /**
     * Creates a new set containing values from both sets.
     *
     * @param IntSet $other
     * @return IntSet
     */
    public function union(IntSet $other): self
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
     * @return IntSet
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
        return array_map('intval', array_keys($this->items));
    }

    /**
     * Returns an iterator for the set's values.
     *
     * @return \Traversable
     */
    public function getIterator(): \Traversable
    {
        foreach (array_keys($this->items) as $value) {
            yield (int)$value;
        }
    }

    /**
     * Returns the minimum value in the set.
     *
     * @return int|null Null if set is empty
     */
    public function min(): ?int
    {
        if ($this->isEmpty()) {
            return null;
        }
        
        $keys = array_keys($this->items);
        return (int)min($keys);
    }

    /**
     * Returns the maximum value in the set.
     *
     * @return int|null Null if set is empty
     */
    public function max(): ?int
    {
        if ($this->isEmpty()) {
            return null;
        }
        
        $keys = array_keys($this->items);
        return (int)max($keys);
    }

    /**
     * Returns the sum of all values in the set.
     *
     * @return int
     */
    public function sum(): int
    {
        return array_sum(array_keys($this->items));
    }
}