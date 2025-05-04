# Unit Testing Plan for IntSet and StringSet Classes in a Shopware 6 Plugin

## Overview

This document outlines a plan for creating unit tests for the `IntSet` and `StringSet` classes within a Shopware 6 plugin environment. These tests will ensure the reliability and correctness of these data structures as they're used throughout the plugin.

## Prerequisites

- Shopware 6 plugin structure set up
- PHPUnit integrated with your plugin (included in Shopware's development environment)
- Basic understanding of PHPUnit and Shopware's testing framework

## Directory Structure

Within your plugin, tests should be organized as follows:

```
TopdataFoundationSW6/
├── src/
│   └── ...
└── tests/
    └── Unit/
        ├── DataStructure/
        │   ├── IntSetTest.php
        │   └── StringSetTest.php
        └── bootstrap.php
```

## Setup Steps

1. **Ensure PHPUnit Configuration**

   Make sure your plugin has a proper `phpunit.xml.dist` file in the root directory:

   ```xml
   <?xml version="1.0" encoding="UTF-8"?>
   <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.3/phpunit.xsd"
            bootstrap="tests/bootstrap.php"
            colors="true">
       <testsuites>
           <testsuite name="Unit">
               <directory>tests/Unit</directory>
           </testsuite>
       </testsuites>
   </phpunit>
   ```

2. **Create Test Bootstrap File**

   Create `tests/bootstrap.php` to set up the test environment:

   ```php
   <?php
   require_once __DIR__ . '/../vendor/autoload.php';
   // Any additional setup for your plugin tests
   ```

## Test Cases for IntSet

Create `tests/Unit/DataStructure/IntSetTest.php` with the following test cases:

1. **Basic Operations**
    - Test constructor with empty array
    - Test constructor with preset values
    - Test `add()` method with single and multiple values
    - Test `remove()` method with existing and non-existing values
    - Test `contains()` method with true and false cases

2. **Set Operations**
    - Test `union()` with overlapping and disjoint sets
    - Test `intersect()` with overlapping and disjoint sets
    - Test `diff()` with overlapping and disjoint sets

3. **Helper Methods**
    - Test `isEmpty()` on empty and non-empty sets
    - Test `count()` after various operations
    - Test `clear()` and verify state
    - Test `copy()` and ensure it's a deep copy
    - Test `values()` returns correct array representation

4. **Integer-Specific Methods**
    - Test `min()` and `max()` with various sets
    - Test `sum()` for correct addition
    - Test edge cases (empty set, single element set)

5. **Iterator Functionality**
    - Test `foreach` iteration works correctly
    - Verify iterator returns elements in consistent order

## Test Cases for StringSet

Create `tests/Unit/DataStructure/StringSetTest.php` with similar test cases:

1. **Basic Operations**
    - Test constructor with empty array
    - Test constructor with preset string values
    - Test `add()` method with single and multiple strings
    - Test `remove()` method with existing and non-existing strings
    - Test `contains()` method with true and false cases

2. **Set Operations**
    - Test `union()` with overlapping and disjoint sets
    - Test `intersect()` with overlapping and disjoint sets
    - Test `diff()` with overlapping and disjoint sets

3. **Helper Methods**
    - Test `isEmpty()` on empty and non-empty sets
    - Test `count()` after various operations
    - Test `clear()` and verify state
    - Test `copy()` and ensure it's a deep copy
    - Test `values()` returns correct array representation

4. **Iterator Functionality**
    - Test `foreach` iteration works correctly
    - Verify iterator returns elements in consistent order

5. **Binary UUID Handling** (specific to your use case)
    - Test with actual binary UUID strings
    - Verify performance with large sets of UUIDs

## Integration with Shopware

For Shopware 6 specific testing:

1. **Plugin Service Container Tests**
    - Test registering your sets as services in the DI container
    - Test injecting your sets into other services

2. **Performance Tests** (optional)
    - Create benchmark tests for operations on large sets
    - Compare performance with alternative approaches

## Sample Test Implementation

Here's a sample implementation for `IntSetTest.php`:

```php
<?php declare(strict_types=1);

namespace TopdataFoundationSW6\Tests\Unit\DataStructure;

use PHPUnit\Framework\TestCase;
use TopdataFoundationSW6\DataStructure\IntSet;

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
        $this->assertTrue($set->contains(1, 2, 3));
    }
    
    public function testAddMethod(): void
    {
        $set = new IntSet();
        $set->add(5);
        $this->assertTrue($set->contains(5));
        
        $set->add(10, 15, 20);
        $this->assertTrue($set->contains(5, 10, 15, 20));
        $this->assertCount(4, $set);
    }
    
    public function testRemoveMethod(): void
    {
        $set = new IntSet([1, 2, 3, 4, 5]);
        $set->remove(3);
        $this->assertFalse($set->contains(3));
        $this->assertTrue($set->contains(1, 2, 4, 5));
        
        $set->remove(10); // Non-existing value
        $this->assertCount(4, $set);
    }
    
    public function testUnionOperation(): void
    {
        $set1 = new IntSet([1, 2, 3]);
        $set2 = new IntSet([3, 4, 5]);
        
        $union = $set1->union($set2);
        
        $this->assertCount(5, $union);
        $this->assertTrue($union->contains(1, 2, 3, 4, 5));
        
        // Original sets should be unchanged
        $this->assertCount(3, $set1);
        $this->assertCount(3, $set2);
    }
    
    // Add more test methods for other operations...
}
```

## Running Tests

Run your tests with the following command from your plugin directory:

```bash
./vendor/bin/phpunit
```

## Continuous Integration

Consider setting up automatic test execution:

1. Add test running to your CI pipeline (GitHub Actions, GitLab CI, etc.)
2. Configure test coverage reports
3. Set minimum coverage thresholds

## Best Practices

1. Test each method thoroughly with normal cases, edge cases, and error cases
2. Use data providers for tests with multiple similar test cases
3. Ensure tests are isolated and don't depend on each other
4. Use descriptive test method names that indicate what's being tested
5. Keep tests fast - avoid unnecessary operations in test methods

## Documentation

After implementing tests:

1. Document how to run and extend tests
2. Include code coverage statistics
3. Make sure your team knows how to run the tests

This testing approach will help ensure your `IntSet` and `StringSet` classes work correctly and reliably within your Shopware 6 plugin.