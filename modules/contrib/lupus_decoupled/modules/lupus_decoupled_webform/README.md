## INTRODUCTION

The Lupus Decoupled Webform module provides support for [Webform](https://www.drupal.org/project/webform)
module.

Webforms get custom elements API endpoint. The process is described in parent
module Lupus Decoupled Forms. For more information see its [README](../lupus_decoupled_form/README.md).

## REQUIREMENTS

- drupal/webform: ^6.0

## Limitations

Drupal libraries and JS are currently not provided by the webform API response.

We need add documentation about the limitations around frontend drupal
libraries / JS being not set and how to use it. Generally, longer usage
documentation would fit best at lupus-decoupled-website though? So maybe just
add a pointer here. And/or keep technical in-depth details here and only make
a nice usage guide at the docs website

### Confirmation

Inline and page (default) confirmations are supported, but for page
confirmation type user (anonymous as well) needs to have access to view own
webform submissions set on webform settings configured at
`admin/structure/webform/manage/{webform}/access`.
