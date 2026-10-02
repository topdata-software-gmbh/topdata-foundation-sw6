# topdata-plugin-intro

A Shopware 6 admin component that renders a branded Topdata banner card in a plugin's settings page. It provides documentation links, a website link, and the Topdata logo — with automatic German/English localization based on the admin user's locale.

## Preview

The banner renders a card with:
- A green header bar ("Topdata Plugins & Services")
- A body with a short description, two buttons (Documentation / Visit website), and the Topdata logo

Card chrome (shadow, border, background) is stripped by the component so it blends cleanly into the settings page.

## Link resolution

The **Documentation** button resolves in this order (`DOC_BASE_URL` in `index.ts`):

| Prop | Result |
|---|---|
| `docUrl` | used verbatim |
| `pluginName` | `https://docs-v2.topinfra.de/manuals/<lowercased pluginName>/` |
| neither | `https://docs-v2.topinfra.de/` (the manual index) |

The manual URL is derived because that is how the docs site derives the slug: the last segment
of `extra.shopware-plugin-class` (`TopdataTopFeedSW6`) lowercased (`topdatatopfeedsw6`). It only
resolves to a real page when the plugin actually has a manual on the site — otherwise use
`docUrl` with an explicit URL or omit both props to land on the manual index.

`DOC_BASE_URL` is a single constant: change it there when the docs domain moves.

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

When only `<name>configIntro</name>` is provided, the Documentation button links to
`https://docs-v2.topinfra.de/` (the manual index).

### With `pluginName` prop

Pass a `pluginName` to auto-generate the plugin's manual URL on the docs site
(`https://docs-v2.topinfra.de/manuals/<lowercased pluginName>/`).

```xml
<card>
    <title>Topdata</title>
    <title lang="de-DE">Topdata</title>
    <component name="topdata-plugin-intro">
        <name>configIntro</name>
        <pluginName>MyPluginName</pluginName>
    </component>
</card>
```

This renders a "Documentation" button linking to
`https://docs-v2.topinfra.de/manuals/myplugin/` — for a real plugin, the lowercased
plugin class, e.g. `pluginName` `TopdataProductWatchSW6` →
`https://docs-v2.topinfra.de/manuals/topdataproductwatchsw6/`.

### With `docUrl` prop (custom URL)

Pass a `docUrl` to override the documentation link entirely. This takes precedence over `pluginName`.

```xml
<card>
    <title>Topdata</title>
    <title lang="de-DE">Topdata</title>
    <component name="topdata-plugin-intro">
        <name>configIntro</name>
        <docUrl>https://docs-v2.topinfra.de/manuals/topdatacompareproducts/</docUrl>
    </component>
</card>
```

This is what plugins with a manual on the docs site should use: `docUrl` points at
`https://docs-v2.topinfra.de/manuals/<slug>/`, where `<slug>` is the **lowercased plugin class
name** (last segment of `extra.shopware-plugin-class`), e.g. `TopdataCompareProducts` →
`/manuals/topdatacompareproducts/`.

### With both props

When both are provided, `docUrl` wins — `pluginName` is ignored for the link.

```xml
<component name="topdata-plugin-intro">
    <name>configIntro</name>
    <pluginName>MyPlugin</pluginName>
    <docUrl>https://docs-v2.topinfra.de/manuals/myplugin/</docUrl>
</component>
```

> **Props are direct children of `<component>`, not wrapped in `<config>`.** Shopware passes
> them as component attributes; anything nested in a `<config>` element is ignored and the
> component silently falls back to the manual index.

## Props

| Prop | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| `pluginName` | String | No | `''` | Plugin class name; builds the manual URL from its lowercased form |
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
3. Set `docUrl` to the manual URL if the plugin has a manual on the docs site, or set
   `pluginName` to the plugin class to have it derived. Without either, the button lands on
   the manual index.
4. Add subsequent cards for your plugin's own config fields below it.
