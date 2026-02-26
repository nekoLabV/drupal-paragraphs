# INTRODUCTION

The Lupus Decoupled API Log module extend
[REST Log](https://www.drupal.org/project/rest_log) module to log requests from
Lupus Decoupled API.

It is intended to be used as a tool for developers to have a quick way to check
requests and responses to decoupled endpoint.

## REQUIREMENTS

- [REST Log](https://www.drupal.org/project/rest_log)

## INSTALLATION

Make sure that REST Log is available on your system. (It is added as a dev
dependency in `composer.json`).

## Usage

1. Configure REST Log at `/admin/config/development/logging/rest_log`.
2. Investigate API logs at `/admin/reports/rest_log`

# Additional documentation

Check [Debugging topic on official Lupus Decoupled Drupal documentation website](https://lupus-decoupled.org/advanced-topics/debugging).
