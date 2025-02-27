<?php

namespace Topdata\TopdataFoundationSW6\Util;

/**
 * UtilMarkdown - A utility class for colorizing Markdown in CLI
 *
 * This class provides methods to render Markdown with ANSI colors
 * for command-line interfaces, similar to tools like 'glow' or 'mdcat'.
 *
 * @since 1.0.5
 */
class UtilMarkdown
{
    /**
     * ANSI color codes for styling terminal output
     * @var array
     */
    private static $colors = [
        'reset'      => "\033[0m",
        'bold'       => "\033[1m",
        'italic'     => "\033[3m",
        'underline'  => "\033[4m",
        'black'      => "\033[30m",
        'red'        => "\033[31m",
        'green'      => "\033[32m",
        'yellow'     => "\033[33m",
        'blue'       => "\033[34m",
        'magenta'    => "\033[35m",
        'cyan'       => "\033[36m",
        'white'      => "\033[37m",
        'bg_black'   => "\033[40m",
        'bg_green'   => "\033[42m",
        'bg_yellow'  => "\033[43m",
        'bg_blue'    => "\033[44m",
        'bg_magenta' => "\033[45m",
        'bg_cyan'    => "\033[46m",
        'bg_white'   => "\033[47m",
    ];

    /**
     * Color schemes for different markdown elements
     * @var array
     */
    private static $colorScheme;

    /**
     * Initialize the color scheme
     *
     * @param array $customColorScheme Optional custom color scheme
     */
    public static function initialize(array $customColorScheme = [])
    {
        // Default color scheme
        self::$colorScheme = [
            'h1'          => self::$colors['bold'] . self::$colors['cyan'],
            'h2'          => self::$colors['bold'] . self::$colors['green'],
            'h3'          => self::$colors['bold'] . self::$colors['yellow'],
            'h4'          => self::$colors['bold'] . self::$colors['magenta'],
            'h5'          => self::$colors['bold'] . self::$colors['blue'],
            'h6'          => self::$colors['bold'] . self::$colors['red'],
            'bold'        => self::$colors['bold'],
            'italic'      => self::$colors['italic'],
            'code'        => self::$colors['bg_black'] . self::$colors['white'],
            'blockquote'  => self::$colors['green'],
            'list_bullet' => self::$colors['cyan'],
            'list_number' => self::$colors['yellow'],
            'link'        => self::$colors['underline'] . self::$colors['blue'],
            'link_url'    => self::$colors['italic'],
            'hr'          => self::$colors['yellow'],
        ];

        // Override with any custom color schemes
        foreach ($customColorScheme as $key => $value) {
            if (isset(self::$colorScheme[$key])) {
                self::$colorScheme[$key] = $value;
            }
        }
    }

    /**
     * Colorize markdown text for CLI output
     *
     * @param string $markdown The markdown text to colorize
     * @return string The colorized text ready for CLI output
     */
    public static function colorize($markdown)
    {
        // Initialize if not already done
        if (empty(self::$colorScheme)) {
            self::initialize();
        }

        // Headers (# Header)
        $markdown = preg_replace_callback('/^(#{1,6})\s+(.+?)$/m', function ($matches) {
            $level = strlen($matches[1]);
            $text = $matches[2];
            $headerColor = self::$colorScheme['h' . $level];
            return $headerColor . strtoupper($text) . self::$colors['reset'] . "\n";
        }, $markdown);

        // Bold (**text** or __text__)
        $markdown = preg_replace('/\*\*(.*?)\*\*|__(.*?)__/s',
            self::$colorScheme['bold'] . '$1$2' . self::$colors['reset'], $markdown);

        // Italic (*text* or _text_)
        $markdown = preg_replace('/\*(.*?)\*|_(.*?)_/s',
            self::$colorScheme['italic'] . '$1$2' . self::$colors['reset'], $markdown);

        // Inline code (`code`)
        $markdown = preg_replace('/`(.*?)`/s',
            self::$colorScheme['code'] . '$1' . self::$colors['reset'], $markdown);

        // Code blocks (```)
        $markdown = preg_replace_callback('/```(?:\w+)?\n(.*?)```/s', function ($matches) {
            $code = $matches[1];
            $lines = explode("\n", $code);
            $coloredCode = '';

            foreach ($lines as $line) {
                $coloredCode .= self::$colorScheme['code'] . $line . self::$colors['reset'] . "\n";
            }

            return $coloredCode;
        }, $markdown);

        // Blockquotes (> text)
        $markdown = preg_replace_callback('/^>\s+(.+?)$/m', function ($matches) {
            return self::$colorScheme['blockquote'] . '│ ' . $matches[1] . self::$colors['reset'] . "\n";
        }, $markdown);

        // Lists (- item or * item or 1. item)
        $markdown = preg_replace_callback('/^(\s*)([*\-+]|\d+\.)\s+(.+?)$/m', function ($matches) {
            $indent = $matches[1];
            $bullet = $matches[2];
            $text = $matches[3];

            if (is_numeric(rtrim($bullet, '.'))) {
                // Numbered list
                return $indent . self::$colorScheme['list_number'] . $bullet . ' ' .
                    self::$colors['reset'] . $text . "\n";
            } else {
                // Bulleted list
                return $indent . self::$colorScheme['list_bullet'] . '• ' .
                    self::$colors['reset'] . $text . "\n";
            }
        }, $markdown);

        // Links [text](url)
        $markdown = preg_replace('/\[(.*?)\]\((.*?)\)/s',
            self::$colorScheme['link'] . '$1' . self::$colors['reset'] . ' ' .
            self::$colorScheme['link_url'] . '($2)' . self::$colors['reset'], $markdown);

        // Horizontal rules (---, ___, ***)
        $markdown = preg_replace('/^[\*\-_]{3,}$/m',
            self::$colorScheme['hr'] . '―――――――――――――――――――――――――' . self::$colors['reset'] . "\n", $markdown);

        return $markdown;
    }

    /**
     * Print colorized markdown to the terminal
     *
     * @param string $markdown The markdown text to print
     */
    public static function render($markdown)
    {
        echo self::colorize($markdown);
    }

    /**
     * Read markdown from a file and colorize it
     *
     * @param string $filePath Path to the markdown file
     * @return string Colorized markdown or error message
     */
    public static function renderFile($filePath)
    {
        if (file_exists($filePath)) {
            $markdown = file_get_contents($filePath);
            return self::colorize($markdown);
        } else {
            return "Error: File not found: $filePath\n";
        }
    }

    /**
     * Get a demo/example of colorized markdown
     *
     * @return string Colorized example markdown
     */
    public static function getDemo()
    {
        $exampleMarkdown = <<<MARKDOWN
# UtilMarkdown Demo

This is a **bold** statement with some *italic* text and `inline code`.

## Features

- Colorizes headers
- Supports **bold** and *italic* text
- Handles `inline code` and code blocks
- Processes > blockquotes
- Works with lists:
  1. Numbered items
  2. With proper indentation
- Renders [links](https://example.com)

### Code Example

```php
class Demo {
    public static function hello() {
        echo "Hello, world!";
    }
}
```

> This is a blockquote with some important information.

---

Made with PHP
MARKDOWN;

        return self::colorize($exampleMarkdown);
    }

    /**
     * Set a custom color for a specific element
     *
     * @param string $element Element name (e.g., 'h1', 'bold', etc.)
     * @param string $color ANSI color code or combination
     */
    public static function setElementColor($element, $color)
    {
        // Initialize if not already done
        if (empty(self::$colorScheme)) {
            self::initialize();
        }

        if (isset(self::$colorScheme[$element])) {
            self::$colorScheme[$element] = $color;
        }
    }

    /**
     * Get all available ANSI color codes
     *
     * @return array Array of available color codes
     */
    public static function getAvailableColors()
    {
        return self::$colors;
    }
}

// ==== Example usage ====
//
// if (php_sapi_name() === 'cli' && basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
//     // Initialize UtilMarkdown
//     UtilMarkdown::initialize();
//
//     // If a file path is provided, read and display the file
//     if (isset($argv[1])) {
//         $file = $argv[1];
//         echo UtilMarkdown::renderFile($file);
//     } else {
//         // Otherwise show demo
//         echo UtilMarkdown::getDemo();
//         echo "\n";
//         echo "Usage: php " . basename(__FILE__) . " [markdown_file.md]\n";
//     }
// }