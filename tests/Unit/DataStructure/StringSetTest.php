<?php declare(strict_types=1);

namespace TopdataFoundationSW6\Tests\Unit\DataStructure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Topdata\TopdataFoundationSW6\DataStructure\StringSet;

/**
 * @covers \Topdata\TopdataFoundationSW6\DataStructure\StringSet
 */
class StringSetTest extends TestCase
{
    /**
     * Test constructor with an empty array.
     */
    public function testConstructorEmpty(): void
    {
        $set = new StringSet([]);
        $this->assertTrue($set->isEmpty());
        $this->assertCount(0, $set);
    }

    /**
     * Test constructor with preset string values.
     */
    public function testConstructorPreset(): void
    {
        $values = ['apple', 'banana', 'cherry'];
        $set = new StringSet($values);
        $this->assertFalse($set->isEmpty());
        $this->assertCount(3, $set);
        $this->assertTrue($set->contains('apple'));
        $this->assertTrue($set->contains('banana'));
        $this->assertTrue($set->contains('cherry'));
        $this->assertFalse($set->contains('date'));
    }

    /**
     * Test add() method with single and multiple strings.
     */
    public function testAdd(): void
    {
        $set = new StringSet();
        $set->add('apple');
        $this->assertCount(1, $set);
        $this->assertTrue($set->contains('apple'));

        $set->add('banana');
        $this->assertCount(2, $set);
        $this->assertTrue($set->contains('banana'));

        // Add existing value
        $set->add('apple');
        $this->assertCount(2, $set); // Count should not change

        // Add multiple values
        $set->add('cherry', 'date');
        $this->assertCount(4, $set);
        $this->assertTrue($set->contains('cherry'));
        $this->assertTrue($set->contains('date'));
    }

    /**
     * Test remove() method with existing and non-existing strings.
     */
    public function testRemove(): void
    {
        $set = new StringSet(['apple', 'banana', 'cherry']);
        $this->assertCount(3, $set);

        $set->remove('banana');
        $this->assertCount(2, $set);
        $this->assertFalse($set->contains('banana'));
        $this->assertTrue($set->contains('apple'));
        $this->assertTrue($set->contains('cherry'));

        // Remove non-existing value
        $set->remove('date');
        $this->assertCount(2, $set); // Count should not change

        // Remove multiple values
        $set->remove('apple', 'cherry');
        $this->assertCount(0, $set);
        $this->assertTrue($set->isEmpty());
    }

    /**
     * Test contains() method with true and false cases.
     */
    public function testContains(): void
    {
        $set = new StringSet(['apple', 'banana']);
        $this->assertTrue($set->contains('apple'));
        $this->assertTrue($set->contains('banana'));
        $this->assertFalse($set->contains('cherry'));
        $this->assertFalse($set->contains(''));
    }

    /**
     * Test union() with overlapping sets.
     */
    public function testUnionOverlapping(): void
    {
        $set1 = new StringSet(['apple', 'banana']);
        $set2 = new StringSet(['banana', 'cherry']);
        $unionSet = $set1->union($set2);

        $this->assertCount(3, $unionSet);
        $this->assertTrue($unionSet->contains('apple'));
        $this->assertTrue($unionSet->contains('banana'));
        $this->assertTrue($unionSet->contains('cherry'));

        // Ensure original sets are not modified
        $this->assertCount(2, $set1);
        $this->assertCount(2, $set2);
    }

    /**
     * Test union() with disjoint sets.
     */
    public function testUnionDisjoint(): void
    {
        $set1 = new StringSet(['apple', 'banana']);
        $set2 = new StringSet(['cherry', 'date']);
        $unionSet = $set1->union($set2);

        $this->assertCount(4, $unionSet);
        $this->assertTrue($unionSet->contains('apple'));
        $this->assertTrue($unionSet->contains('banana'));
        $this->assertTrue($unionSet->contains('cherry'));
        $this->assertTrue($unionSet->contains('date'));
    }

    /**
     * Test intersect() with overlapping sets.
     */
    public function testIntersectOverlapping(): void
    {
        $set1 = new StringSet(['apple', 'banana', 'cherry']);
        $set2 = new StringSet(['banana', 'cherry', 'date']);
        $intersectSet = $set1->intersect($set2);

        $this->assertCount(2, $intersectSet);
        $this->assertFalse($intersectSet->contains('apple'));
        $this->assertTrue($intersectSet->contains('banana'));
        $this->assertTrue($intersectSet->contains('cherry'));
        $this->assertFalse($intersectSet->contains('date'));

        // Ensure original sets are not modified
        $this->assertCount(3, $set1);
        $this->assertCount(3, $set2);
    }

    /**
     * Test intersect() with disjoint sets.
     */
    public function testIntersectDisjoint(): void
    {
        $set1 = new StringSet(['apple', 'banana']);
        $set2 = new StringSet(['cherry', 'date']);
        $intersectSet = $set1->intersect($set2);

        $this->assertCount(0, $intersectSet);
        $this->assertTrue($intersectSet->isEmpty());
    }

    /**
     * Test diff() with overlapping sets.
     */
    public function testDiffOverlapping(): void
    {
        $set1 = new StringSet(['apple', 'banana', 'cherry']);
        $set2 = new StringSet(['banana', 'date']);
        $diffSet = $set1->diff($set2);

        $this->assertCount(2, $diffSet);
        $this->assertTrue($diffSet->contains('apple'));
        $this->assertFalse($diffSet->contains('banana'));
        $this->assertTrue($diffSet->contains('cherry'));
        $this->assertFalse($diffSet->contains('date'));

        // Ensure original sets are not modified
        $this->assertCount(3, $set1);
        $this->assertCount(2, $set2);
    }

    /**
     * Test diff() with disjoint sets.
     */
    public function testDiffDisjoint(): void
    {
        $set1 = new StringSet(['apple', 'banana']);
        $set2 = new StringSet(['cherry', 'date']);
        $diffSet = $set1->diff($set2);

        $this->assertCount(2, $diffSet);
        $this->assertTrue($diffSet->contains('apple'));
        $this->assertTrue($diffSet->contains('banana'));
        $this->assertFalse($diffSet->contains('cherry'));
        $this->assertFalse($diffSet->contains('date'));
    }

    /**
     * Test isEmpty() on empty and non-empty sets.
     */
    public function testIsEmpty(): void
    {
        $set = new StringSet();
        $this->assertTrue($set->isEmpty());

        $set->add('apple');
        $this->assertFalse($set->isEmpty());

        $set->remove('apple');
        $this->assertTrue($set->isEmpty());
    }

    /**
     * Test count() after various operations.
     */
    public function testCount(): void
    {
        $set = new StringSet();
        $this->assertCount(0, $set);

        $set->add('apple');
        $this->assertCount(1, $set);

        $set->add('banana', 'cherry');
        $this->assertCount(3, $set);

        $set->remove('banana');
        $this->assertCount(2, $set);

        $set->clear();
        $this->assertCount(0, $set);
    }

    /**
     * Test clear() and verify state.
     */
    public function testClear(): void
    {
        $set = new StringSet(['apple', 'banana']);
        $this->assertFalse($set->isEmpty());
        $this->assertCount(2, $set);

        $set->clear();
        $this->assertTrue($set->isEmpty());
        $this->assertCount(0, $set);
        $this->assertFalse($set->contains('apple'));
        $this->assertFalse($set->contains('banana'));
    }

    /**
     * Test copy() and ensure it's a deep copy.
     */
    public function testCopy(): void
    {
        $set = new StringSet(['apple', 'banana']);
        $copySet = $set->copy();

        $this->assertCount(2, $copySet);
        $this->assertTrue($copySet->contains('apple'));
        $this->assertTrue($copySet->contains('banana'));

        // Ensure it's a deep copy by modifying the original
        $set->add('cherry');
        $this->assertCount(3, $set);
        $this->assertCount(2, $copySet); // Copy should not be affected
        $this->assertFalse($copySet->contains('cherry'));
    }

    /**
     * Test values() returns correct array representation.
     */
    public function testValues(): void
    {
        $values = ['apple', 'banana', 'cherry'];
        $set = new StringSet($values);
        $retrievedValues = $set->values();

        // Order might not be guaranteed depending on internal implementation (e.g., using array keys)
        // So, we'll check if the arrays contain the same elements regardless of order.
        $this->assertCount(3, $retrievedValues);
        $this->assertEmpty(array_diff($values, $retrievedValues));
        $this->assertEmpty(array_diff($retrievedValues, $values));

        $emptySet = new StringSet();
        $this->assertEmpty($emptySet->values());
    }

    /**
     * Test foreach iteration works correctly and in consistent order.
     */
    public function testIterator(): void
    {
        $values = ['apple', 'banana', 'cherry'];
        $set = new StringSet($values);

        $iteratedValues = [];
        foreach ($set as $value) {
            $iteratedValues[] = $value;
        }

        // Check if all values are present
        $this->assertCount(3, $iteratedValues);
        $this->assertEmpty(array_diff($values, $iteratedValues));
        $this->assertEmpty(array_diff($iteratedValues, $values));

        // Check for consistent order (requires the StringSet implementation to maintain order)
        // If the implementation uses a hash map or similar, order is not guaranteed.
        // Assuming the implementation maintains insertion order or sorts internally for consistency.
        // If order is not guaranteed, this assertion might need adjustment or removal.
        // For now, let's assume a consistent order (e.g., insertion order).
        // A more robust test might involve sorting both arrays before comparison if order isn't a strict requirement of StringSet.
        // For this test, let's assume insertion order is maintained.
        $this->assertSame($values, $iteratedValues);
    }

    /**
     * Test with actual binary UUID strings.
     * Note: This assumes the StringSet can handle binary strings correctly.
     * Performance testing with large sets is not included in this basic test file.
     */
    public function testBinaryUuidHandling(): void
    {
        // Example binary UUIDs (these are just examples, actual binary UUIDs would be different)
        $uuid1 = hex2bin(str_replace('-', '', 'f47ac10b-58cc-4372-a567-0e02b2c3d479'));
        $uuid2 = hex2bin(str_replace('-', '', 'c6a1b2d3-e4f5-6789-0123-456789abcdef'));
        $uuid3 = hex2bin(str_replace('-', '', '123e4567-e89b-12d3-a456-426614174000'));

        $set = new StringSet([$uuid1, $uuid2]);
        $this->assertCount(2, $set);
        $this->assertTrue($set->contains($uuid1));
        $this->assertTrue($set->contains($uuid2));
        $this->assertFalse($set->contains($uuid3));

        $set->add($uuid3);
        $this->assertCount(3, $set);
        $this->assertTrue($set->contains($uuid3));

        $set->remove($uuid2);
        $this->assertCount(2, $set);
        $this->assertFalse($set->contains($uuid2));

        $values = $set->values();
        $this->assertCount(2, $values);
        $this->assertContains($uuid1, $values);
        $this->assertContains($uuid3, $values);
    }
}