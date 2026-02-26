# Lupus Decoupled Site Info

This module allows exposing configuration as a REST resource at `/api/site-info`.

## Configuration

Customize a list of config values to expose into the `lupus_decoupled_site_info.settings`.

    drush config:edit lupus_decoupled_site_info.settings

The format for each exposed value should be `{config_name}:{config_key}`.
