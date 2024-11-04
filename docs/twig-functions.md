# Twig Functions for Configuration Access

The TopdataControlCenterSW6 plugin provides several Twig functions to access plugin configurations in your templates.

## Available Functions

### topConfig(pluginName, key)
Get a string configuration value. Returns an empty string if the key is not found.

```twig
{{ topConfig('MyPlugin', 'settings.apiKey') }}
```

### topConfigBool(pluginName, key)
Get a boolean configuration value. Returns false if the key is not found.

```twig
{% if topConfigBool('MyPlugin', 'features.enableNewUI') %}
    {# Show new UI #}
{% endif %}
```

### topConfigInt(pluginName, key)
Get an integer configuration value. Returns 0 if the key is not found.

```twig
{% set limit = topConfigInt('MyPlugin', 'pagination.itemsPerPage') %}
```

### topConfigTree(pluginName)
Get the complete configuration tree for a plugin. Returns an empty array if the plugin is not registered.

```twig
{% set config = topConfigTree('MyPlugin') %}
{{ config.features.enableNewUI }}
{{ config.pagination.itemsPerPage }}
```

## Error Handling

All functions handle errors gracefully by returning default values:
- String functions return an empty string
- Boolean functions return false
- Integer functions return 0
- Tree function returns an empty array

This prevents template errors when configurations are missing or invalid.
