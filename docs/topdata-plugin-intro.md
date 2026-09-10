# topdata-plugin-intro

A Shopware 6 admin component that renders a branded Topdata banner card in a plugin's settings page. It provides documentation links, a website link, and the Topdata logo — with automatic German/English localization based on the admin user's locale.

## Preview

The banner renders a card with:
- A green header bar ("Topdata Plugins & Services")
- A body with a short description, two buttons (Documentation / Visit website), and the Topdata logo

Card chrome (shadow, border, background) is stripped by the component so it blends cleanly into the settings page.

## Usage in `config.xml`

Reference the component inside a `<card>` element. The component is provided by the foundation plugin and is available to all dependent plugins at runtime.

### Minimal (default docs URL)

```xml
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/shopware/platform/trunk/src/Core/System/SystemConfig/Schema/config.xsd">
    <card>
        <title>Topdata</title>
        <title lang="de-DE">Topdata</title>
        <component name="topdata-plugin-intro">
            <name>configIntro</name>
        </component>
    </card>
</config>
```

When only `<name>configIntro</name>` is provided, the Documentation button links to `https://topdata.de` (fallback).

### With `pluginName` prop

Pass a `pluginName` to auto-generate a documentation URL in the format `https://topdata.de/dokumentation/{pluginName}`.

```xml
<card>
    <title>Topdata</title>
    <title lang="de-DE">Topdata</title>
    <component name="topdata-plugin-intro">
        <name>configIntro</name>
        <config>
            <pluginName>MyPluginName</pluginName>
        </config>
    </component>
</card>
```

This renders a "Documentation" button linking to `https://topdata.de/dokumentation/MyPluginName`.

### With `docUrl` prop (custom URL)

Pass a `docUrl` to override the documentation link entirely. This takes precedence over `pluginName`.

```xml
<card>
    <title>Topdata</title>
    <title lang="de-DE">Topdata</title>
    <component name="topdata-plugin-intro">
        <name>configIntro</name>
        <config>
            <docUrl>https://docs.example.com/my-plugin</docUrl>
        </config>
    </component>
</card>
```

### With both props

When both are provided, `docUrl` wins — `pluginName` is ignored for the link.

```xml
<component name="topdata-plugin-intro">
    <name>configIntro</name>
    <config>
        <pluginName>MyPlugin</pluginName>
        <docUrl>https://docs.example.com/custom-path</docUrl>
    </config>
</component>
```

## Props

| Prop | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| `pluginName` | String | No | `''` | Plugin name used to build the docs URL (`https://topdata.de/dokumentation/{pluginName}`) |
| `docUrl` | String | No | `''` | Full documentation URL. Overrides `pluginName` when set. |

## Locale behavior

The component detects the admin user's locale from `Shopware.State.get('session').currentLocale`:

- **German** (`de-*`): Shows German text and "Dokumentation" / "Webseite besuchen" button labels.
- **Everything else**: Shows English text and "Documentation" / "Visit website" button labels.

## Visual styling

- Header bar: Topdata green (`#6c9f5b`)
- Primary button (Documentation): Topdata dark (`#0c213b`) with white text
- Ghost button (Website): outlined in Topdata dark
- The component strips its parent card's shadow, border, and background in `mounted()` so it renders as a flat banner

## Real-world examples

### Topdata Error Collector SW6

The `topdata-error-collector-sw6` plugin uses the banner as the sole content of its settings page (no config fields):

```xml
<!-- src/Resources/config/config.xml -->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/shopware/platform/trunk/src/Core/System/SystemConfig/Schema/config.xsd">
    <card>
        <title>Topdata</title>
        <title lang="de-DE">Topdata</title>
        <component name="topdata-plugin-intro">
            <name>configIntro</name>
        </component>
    </card>
</config>
```

### Topdata Foundation SW6

The foundation plugin itself uses the banner above its own config fields:

```xml
<!-- src/Resources/config/config.xml -->
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="https://raw.githubusercontent.com/shopware/platform/trunk/src/Core/System/SystemConfig/Schema/config.xsd">
    <card>
        <title>Topdata</title>
        <title lang="de-DE">Topdata</title>
        <component name="topdata-plugin-intro">
            <name>configIntro</name>
        </component>
    </card>

    <card>
        <title>Basic Configuration</title>
        <title lang="de-DE">Grundeinstellungen</title>

        <input-field>
            <name>example</name>
            <label>Example Configuration</label>
            <label lang="de-DE">Beispiel Konfiguration</label>
        </input-field>
    </card>
</config>
```

## Adding to a new Topdata plugin

1. Ensure the foundation plugin (`topdata/topdata-foundation-sw6`) is a Composer dependency.
2. Add a `<card>` with the `topdata-plugin-intro` component as the **first card** in your `config.xml`.
3. Optionally set `pluginName` or `docUrl` for the correct documentation link.
4. Add subsequent cards for your plugin's own config fields below it.
