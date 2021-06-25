# Embargoes Log Views

Provides views functionality and a default view for embargo logs.

## Requirements

This module requires the following modules/libraries:

* Embargoes
* The core Drupal views implementation

## Installation

Install as
[usual](https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules).

If Embargoes has already been installed and in use, run Drupal database updates
to ensure the database is current.

## Usage

The default view can be accessed at `admin/reports/embargoes_logs`. It can be
edited at `admin/structure/views/view/embargoes_logs`.

You can create more log views by navigating to `admin/structure/views/add` and
selecting 'Embargoes log entries' from the 'Show:' drop-down menu under 'View
Settings'.

## License
[GPLv2](http://www.gnu.org/licenses/gpl-2.0.txt)
