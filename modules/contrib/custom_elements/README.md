# Custom elements

## Introduction

The Custom Elements module provides the framework for rendering Drupal data
(entities, fields, ...) into custom elements markup. Custom elements can be
easily rendered by frontend components, e.g. via web components or various
Javascript frontend frameworks. This enables Drupal to render into high-level
theme components, while the actually rendering of the components is handled by
a frontend application (possibly in the browser).

The Custom Elements module provides
 * the UI to configure the custom element output for entities by view-mode (3.x only)
 * the API to build a (nested tree) of custom element objects, with associated
cache metadata
 * the API to serialize a tree of custom objects into markup or into
a JSON representation
 * the API for other modules to customize how data is rendered into custom
elements via Custom element processors

### Frontend rendering

Today's browsers provide [an API](https://html.spec.whatwg.org/multipage/custom-elements.html#custom-elements-autonomous-example)
for developers to define their own HTML elements, like
`<flag-icon country="nl"></flag-icon>`. Besides that, many frontend frameworks
render their components using the same, or similar custom elements syntax.
That way, we can render a custom element with [Web components](https://developer.mozilla.org/de/docs/Web/Web_Components) or any component-oriented frontend
framework, like [Vue.js](https://vuejs.org/) or [React](https://react.dev/).

## Usage

### Progressive decoupling

Custom elements output may be used as part of a regular Drupal-rendered
response, where some JavaScript or Web-Components take over the rendering in
the browser. For that use case, any Javascript libraries needed for rendering
the markup may be added to the `custom_elements/main` library, e.g. in a custom
theme. This library is attached to every custom element markup rendered via
Drupal.

To use custom element markup for outputting specific entity bundles / view modes,
enable the "Force custom elements rendering" checkbox on their "Manage Display" tab.

### Full decoupling

Alternatively, the module can be used in conjunction via the Lupus Custom Elements Renderer
or Lupus Decoupled Drupal modules. The [Lupus Custom Elements Renderer](https://www.drupal.org/project/lupus_ce_renderer)
module switches Drupal's main content renderer to provide API responses using
custom elements markup or a custom elements JSON serialization for complete
pages.

### Lupus Decoupled Drupal

Lupus Decoupled Drupal builds upon Lupus Custom Elements Renderer and packages
it up into an easily usable decoupled setup, see https://lupus-decoupled.org

## Custom element render formats

The module supports two main serialization modes:

* Markup - Render custom elements into HTML-like markup.
* JSON - Render a JSON tree representation of custom elements.

The markup variant supports multiple styles to cater for the different needs
of different frameworks.

### Custom Element markup styles

Custom elements use "slots" for handling content distribution, i.e. for passing
nested content to an element. However, the concrete syntax used for handling
slots may differ by various frameworks. Thus, the module supports rendering to
different markup styles while it defaults to the Web component style syntax,
which is supported by Vue 2 as well (via its legacy slot syntax). In addition,
the module supports the [Vue2 and Vue 3](https://vuejs.org/v2/guide/components-slots.html#Named-Slots-Shorthand)
syntax which can be enabled at "Admin > Configuration > System > Custom Elements".

### JSON serialization formats

For JSON API responses, the module supports two formats:

**Explicit format** (default for new installs):
```json
{
  "element": "article-teaser",
  "props": {
    "title": "Title",
    "href": "/article/1"
  },
  "slots": {
    "default": "<p>Content</p>",
    "media": {"element": "image", "props": {"src": "/img.jpg"}}
  }
}
```

**Legacy format** (backward compatibility, supported in 3.x, will be removed in 4.x):
```json
{
  "element": "article-teaser",
  "title": "Title",
  "href": "/article/1",
  "content": "<p>Content</p>",
  "media": {"element": "image", "src": "/img.jpg"}
}
```

Configure via: Admin > Configuration > System > Custom Elements

## Further details

### Methods of rendering custom elements

By default, the module does nothing unless its API is used by other modules
(e.g. the Lupus CE renderer) or it's configured to take over entity rendering
for some entity view modes (per "Progressive decoupling" above).

An entity-view can be rendered into custom elements by one of three methods.
Methods 1 and 2 need a Custom Elements Display (configuration object); this can
be viewed/edited through the Drupal UI after enabling the Custom Elements UI
submodule.

1. Using layouts

This method renders a layout, as configured with Drupal's layout builder, into
`<drupal-layout>` elements and allows the contained blocks to render into
custom elements.

This method is used when two conditions are both met:
* "Use Layout Builder" is enabled for the requested view mode's display (or for
  the default display if none exists for the requested view mode);
* "Use Layout Builder" is enabled for the requested view mode's Custom Elements
  Display (or for the default display if none exists for the requested view
  mode). This option is ignored (and invisible in the UI) if the same option
  isn't enabled in the regular display.

2. Using automatic processing with processors

The module comes with Custom element processors that try to implement
a reasonable default for content entities and fields. These defaults can be
further customized with custom modules.

The default entity processor renders the data of all visible fields either as
attribute to the custom element tag, or as nested markup via a slot. The module
maps simple fields and their properties to attributes and falls back to
rendering more complex fields to regular markup, which gets added as slot to
the parent custom element.

This was the default in version 2.x and is still used when "Use automatic
processing" is enabled in the Custom Elements display.

Processor services can also be used to render individual fields; this is still
the default (i.e. a newly created Custom Elements Display has all fields set
to the "Automatic processing" formatter.)

3. Using components in a Custom Elements Display

A Custom Elements Display contains information to render individual fields
using 'custom element field formatter plugins'; values are formatted and output
as configured by each plugin. This is equivalent to field formatters used by
Drupal's regular displays.

This is the default method in version 3.x. If no Custom Elements Displays are
created yet for a certain entity/bundle, a display is created internally for
rendering, based on fields enabled in the regular display (for the specific
view mode if it's enabled; otherwise for the default view mode). All fields are
rendered in "Automatic processing" mode (using the "Auto" formatter).


## Upgrade from 2.x

## Overview

3.x introduced configuration based rendering based upon Custom Elements Entity
Displays, which is now the primary way render an entity into custom elements.
However, there is the option to enable "Automatic Processing" in the config,
what makes rendering basically work as in 2.x again.

For upgrading, it's a good idea to add custom-elements configuration for your
entity view modes as necessary and to enable "Automatic Processing". Once that
works, it's possible to disable and move to regular formatter-based config any
time.

### Important changes

* Custom Elements Displays with automatic processing enabled don't provide
  perfect backward compatibility, check change notices. For example
  [the "type" attribute is removed](https://www.drupal.org/node/3474724).

* Any view mode that uses Layout Builder to build custom elements, now
  requires a Custom Elements Display that enables this option.


### Submodules

* custom_elements_everywhere:
  The module is gone in 3.x - please uninstall it before updating.
  Instead, simply enable "force custom elements rendering" for all view
  modes as desired. This can be done in the corresponding "Manage display"
  tab in the UI.

* custom_elements_thunder:
  Version 2.x implements a couple of example processors for the thunder
  paragraphs. Those have been removed and replaced by Custom Element
  display configuration that works similarly.
  See [the module's README.md](modules/custom_elements_thunder/README.md)
  for further details.

### API changes

Generally, the core module and its API remain unchanged. Besides that some small API changes /
simplifications have been applied. Please take a list of change records for details:

https://www.drupal.org/list-changes/custom_elements


## Compatibility

| Custom Elements | Drupal Core | PHP   | Canvas (optional)        |
|-----------------|-------------|-------|--------------------------------------|
| 3.x             | 10.x, 11.x  | 8.1+  | 1.x (requires D11.2+ & PHP 8.3+)    |

### Canvas Integration (experimental)

The module includes optional integration with Drupal's Canvas module.
This integration requires:
- Drupal 11.2 or higher
- PHP 8.3 or higher
- Canvas 1.x - latest release

The Canvas integration is optional and only used for development and
testing. The module will work without it.

## Credits

  - [drunomics GmbH](https://www.drupal.org/drunomics): Concept, Development, Maintenance
  - [Österreichischer Wirtschaftsverlag GmbH](https://www.drupal.org/%C3%B6sterreichischer-wirtschaftsverlag-gmbh): Initial sponsor of v1
