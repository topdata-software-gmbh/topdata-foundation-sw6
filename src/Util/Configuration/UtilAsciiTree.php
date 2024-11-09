<?php

namespace Topdata\TopdataFoundationSW6\Util\Configuration;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class UtilAsciiTree
{

    /**
     * tree - Converts a data structure into an ASCII tree
     *
     * recursive function
     *
     * 06/2024 created
     * 07/2024 using now symfony/property-access
     *
     * @param array|object $data The data structure to convert.
     * @param string $prefix Used for formatting the tree (internal use).
     * @return string The ASCII representation of the tree.
     */
    public static function tree(array|object $data, string $prefix = '', ?PropertyAccessor $propertyAccessor = null): string
    {
        if ($propertyAccessor === null) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        $output = '';
        $isLast = fn ($key, $array) => $key === array_key_last($array);

        foreach ($data as $key => $value) {
            $output .= $prefix;
            if ($isLast($key, $data)) {
                $output .= '└── ';
                $newPrefix = $prefix . '    ';
            } else {
                $output .= '├── ';
                $newPrefix = $prefix . '│   ';
            }

            if (is_array($value) || is_object($value)) {
                $output .= "$key\n";
                $output .= self::tree($value, $newPrefix, $propertyAccessor);
            } else {
                $output .= "$key: $value\n";
            }
        }

        return $output;
    }


}