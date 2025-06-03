<?php declare(strict_types=1);

namespace Topdata\TopdataFoundationSW6\Tests\Unit\DataStructure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use Topdata\TopdataFoundationSW6\DataStructure\IntSet;

class IntSetTest extends TestCase
{
    public function testConstructorWithEmptyArray(): void
    {
        $set = new IntSet();
        $this->assertCount(0, $set);
        $this->assertTrue($set->isEmpty());
    }

    public function testConstructorWithValues(): void
    {
        $set = new IntSet([1, 2, 3]);
        $this->assertCount(3, $set);
        $this->assertTrue($set->contains(1));
        $this->assertTrue($set->contains(2));
        $this->assertTrue($set->contains(3));
        $this->assertFalse($set->contains(4));
    }

    public function testAddSingleValue(): void
    {
        $set = new IntSet();
        $set->add(5);
        $this->assertTrue($set->contains(5));
        $this->assertCount(1, $set);
    }

    public function testAddMultipleValues(): void
    {
        $set = new IntSet();
        $set->add(10, 15, 20);
        $this->assertTrue($set->contains(10));
        $this->assertTrue($set->contains(15));
        $this->assertTrue($set->contains(20));
        $this->assertCount(3, $set);
    }

    public function testAddExistingValueDoesNothing(): void
    {
        $set = new IntSet([1, 2]);
        $set->add(1);
        $this->assertCount(2, $set);
        $this->assertTrue($set->contains(1));
        $this->assertTrue($set->contains(2));
    }

    public function testRemoveExistingValue(): void
    {
        $set = new IntSet([1, 2, 3]);
        $set->remove(2);
        $this->assertFalse($set->contains(2));
        $this->assertCount(2, $set);
        $this->assertTrue($set->contains(1));
        $this->assertTrue($set->contains(3));
    }

    public function testRemoveNonExistingValueDoesNothing(): void
    {
        $set = new IntSet([1, 2, 3]);
        $set->remove(4);
        $this->assertCount(3, $set);
        $this->assertTrue($set->contains(1));
        $this->assertTrue($set->contains(2));
        $this->assertTrue($set->contains(3));
    }

    public function testContainsSingleValue(): void
    {
        $set = new IntSet([1, 2, 3]);
        $this->assertTrue($set->contains(2));
        $this->assertFalse($set->contains(4));
    }

    public function testContainsMultipleValues(): void
    {
        $set = new IntSet([1, 2, 3, 4, 5]);
        $this->assertTrue($set->contains(2, 4));
        $this->assertFalse($set->contains(2, 6));
    }

    public function testUnionWithOverlappingSets(): void
    {
        $set1 = new IntSet([1, 2, 3]);
        $set2 = new IntSet([3, 4, 5]);
        $union = $set1->union($set2);

        $this->assertCount(5, $union);
        $this->assertTrue($union->contains(1, 2, 3, 4, 5));
        $this->assertFalse($union->contains(6));

        // Original sets should be unchanged
        $this->assertCount(3, $set1);
        $this->assertCount(3, $set2);
    }

    public function testUnionWithDisjointSets(): void
    {
        $set1 = new IntSet([1, 2]);
        $set2 = new IntSet([3, 4]);
        $union = $set1->union($set2);

        $this->assertCount(4, $union);
        $this->assertTrue($union->contains(1, 2, 3, 4));
        $this->assertFalse($union->contains(5));
    }

    public function testIntersectWithOverlappingSets(): void
    {
        $set1 = new IntSet([1, 2, 3, 4]);
        $set2 = new IntSet([3, 4, 5, 6]);
        $intersect = $set1->intersect($set2);

        $this->assertCount(2, $intersect);
        $this->assertTrue($intersect->contains(3, 4));
        $this->assertFalse($intersect->contains(1, 2, 5, 6));

        // Original sets should be unchanged
        $this->assertCount(4, $set1);
        $this->assertCount(4, $set2);
    }

    public function testIntersectWithDisjointSets(): void
    {
        $set1 = new IntSet([1, 2]);
        $set2 = new IntSet([3, 4]);
        $intersect = $set1->intersect($set2);

        $this->assertCount(0, $intersect);
        $this->assertTrue($intersect->isEmpty());
    }

    public function testDiffWithOverlappingSets(): void
    {
        $set1 = new IntSet([1, 2, 3, 4]);
        $set2 = new IntSet([3, 4, 5, 6]);
        $diff = $set1->diff($set2);

        $this->assertCount(2, $diff);
        $this->assertTrue($diff->contains(1, 2));
        $this->assertFalse($diff->contains(3, 4, 5, 6));

        // Original sets should be unchanged
        $this->assertCount(4, $set1);
        $this->assertCount(4, $set2);
    }

    public function testDiffWithDisjointSets(): void
    {
        $set1 = new IntSet([1, 2]);
        $set2 = new IntSet([3, 4]);
        $diff = $set1->diff($set2);

        $this->assertCount(2, $diff);
        $this->assertTrue($diff->contains(1, 2));
        $this->assertFalse($diff->contains(3, 4));
    }

    public function testIsEmpty(): void
    {
        $set = new IntSet();
        $this->assertTrue($set->isEmpty());
        $set->add(1);
        $this->assertFalse($set->isEmpty());
    }

    public function testCount(): void
    {
        $set = new IntSet();
        $this->assertCount(0, $set);
        $set->add(1);
        $this->assertCount(1, $set);
        $set->add(2, 3);
        $this->assertCount(3, $set);
        $set->remove(1);
        $this->assertCount(2, $set);
        $set->clear();
        $this->assertCount(0, $set);
    }

    public function testClear(): void
    {
        $set = new IntSet([1, 2, 3]);
        $this->assertFalse($set->isEmpty());
        $set->clear();
        $this->assertTrue($set->isEmpty());
        $this->assertCount(0, $set);
    }

    public function testCopyIsDeepCopy(): void
    {
        $set = new IntSet([1, 2, 3]);
        $copy = $set->copy();

        $this->assertNotSame($set, $copy); // Ensure it's a new object
        $this->assertEquals($set->values(), $copy->values()); // Ensure content is the same

        $set->add(4);
        $this->assertFalse($copy->contains(4)); // Ensure copy is independent
    }

    public function testValues(): void
    {
        $set = new IntSet([3, 1, 2]);
        $values = $set->values();
        sort($values); // Sort to ensure consistent order for comparison
        $this->assertEquals([1, 2, 3], $values);

        $emptySet = new IntSet();
        $this->assertEquals([], $emptySet->values());
    }

    public function testMinMaxSum(): void
    {
        $set = new IntSet([5, 1, 10, 2]);
        $this->assertEquals(1, $set->min());
        $this->assertEquals(10, $set->max());
        $this->assertEquals(18, $set->sum());
    }

    public function testMinMaxSumEdgeCases(): void
    {
        $emptySet = new IntSet();
        $this->assertNull($emptySet->min());
        $this->assertNull($emptySet->max());
        $this->assertEquals(0, $emptySet->sum());

        $singleElementSet = new IntSet([7]);
        $this->assertEquals(7, $singleElementSet->min());
        $this->assertEquals(7, $singleElementSet->max());
        $this->assertEquals(7, $singleElementSet->sum());
    }

    public function testIterator(): void
    {
        $set = new IntSet([3, 1, 2]);
        $elements = [];
        foreach ($set as $element) {
            $elements[] = $element;
        }
        sort($elements); // Sort to ensure consistent order for comparison
        $this->assertEquals([1, 2, 3], $elements);
    }
}