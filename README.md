# Embargoes

Adds the ability to manage embargo policies with access restrictions on content.

## Requirements

This module requires the following modules/libraries:

* [Menu UI](https://www.drupal.org/docs/core-modules-and-themes/core-modules/menu-ui-module)

## Installation

Install as
[usual](https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules).

## Configuration

Configuration options can be set at `admin/config/content/embargoes/settings`,
including notification options and IP range settings that can apply to
embargoes.

To add an IP range for use on embargoes, navigate to
`admin/config/content/embargoes/settings/ips` and click 'Add IP range'. Ranges
created via this method can then be used as IP address whitelists when creating
embargoes.

## Usage

### Applying an embargo

An embargo can be applied to an existing node by navigating to
`node/{node_id}/embargoes`. From here, an embargo can be applied if it doesn't
already exist, and existing embargoes can be modified or removed.

## Troubleshooting/Issues

### Public Filesystem

Note that when using the public filesystem for downloads, files that have been
exposed in this way no longer have a say in their access by Drupal, including
embargoes. If files need to be secured in this way, consider using a different
filesystem backend for downloads. These can be configured at
admin/config/media/file-system.

Having problems or solved one? Contact
[discoverygarden](http://support.discoverygarden.ca).

## Maintainers/Sponsors

Current maintainers:

* [discoverygarden](http://www.discoverygarden.ca)

## Development

If you would like to contribute to this module, please check out the helpful
[Documentation](https://github.com/Islandora/islandora/wiki#wiki-documentation-for-developers),
[Developers](http://islandora.ca/developers) section on Islandora.ca and create
an issue, pull request and or contact
[discoverygarden](http://support.discoverygarden.ca).

## License
[GPLv2](http://www.gnu.org/licenses/gpl-2.0.txt)
