# GaugeSupport Plugin Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/)
specification.

--------------------------------------------------------------------------------

## [Unreleased]

### Added

- Add chart with rankings to View Issue page form
  [#38](https://github.com/mantisbt-plugins/GaugeSupport/issues/38)
- Display ranking information in View Issue page
  [#37](https://github.com/mantisbt-plugins/GaugeSupport/issues/37)

### Fixed

- Inclusion of install helper functions disrupts MantisBT admin pages
  [#36](https://github.com/mantisbt-plugins/GaugeSupport/issues/36)


## [2.5.0] - 2020-05-25 

### Added

- New 'withdraw vote' button
  [#19](https://github.com/mantisbt-plugins/GaugeSupport/issues/19)
- Config page: "Reset" button to revert configs to defaults
  [#27](https://github.com/mantisbt-plugins/GaugeSupport/issues/27)
- Support for PostgreSQL (and probably MSSQL and Oracle as well)
  [#4](https://github.com/mantisbt-plugins/GaugeSupport/issues/4)
- Russian translation
  [#35](https://github.com/mantisbt-plugins/GaugeSupport/issues/35)
- View Issue Page button to jump to the voting section 
  [#34](https://github.com/mantisbt-plugins/GaugeSupport/issues/34)

### Changed

- Updated README file and screenshots
  [#1](https://github.com/mantisbt-plugins/GaugeSupport/issues/1)
- New default values for Severity config
  [#17](https://github.com/mantisbt-plugins/GaugeSupport/issues/17)
- Use language strings for plugin title & description
  [#22](https://github.com/mantisbt-plugins/GaugeSupport/issues/22)
- Fix and improve gauge form layout
  [#11](https://github.com/mantisbt-plugins/GaugeSupport/issues/11)
- Mark developers and higher in bold font
  [#24](https://github.com/mantisbt-plugins/GaugeSupport/issues/24)
- Use standard MantisBT core functions and avoid code duplication
  [#15](https://github.com/mantisbt-plugins/GaugeSupport/issues/15)
- Redirect to Manage Plugins page after config update
  [#26](https://github.com/mantisbt-plugins/GaugeSupport/issues/26)
- Improve layout for Rankings page
  [#18](https://github.com/mantisbt-plugins/GaugeSupport/issues/18)
- Use language strings for column labels
  [#22](https://github.com/mantisbt-plugins/GaugeSupport/issues/22)
- Renamed config options
  [#29](https://github.com/mantisbt-plugins/GaugeSupport/issues/29)
- Miscellaneous code cleanup and layout improvements
- Use Plugin API instead of hardcoded paths
  [#32](https://github.com/mantisbt-plugins/GaugeSupport/issues/32)

### Fixed

- Deprecated MantisBT API calls
  [#12](https://github.com/mantisbt-plugins/GaugeSupport/issues/12)
- Application warning when processing a non-existing user
  [#25](https://github.com/mantisbt-plugins/GaugeSupport/issues/25)
- Excel download does not work on Apache
  [#20](https://github.com/mantisbt-plugins/GaugeSupport/issues/20)
- "No data" alert on Ranking page is not working
  [#21](https://github.com/mantisbt-plugins/GaugeSupport/issues/21)
- Do not require MySQL SUPER privileges to execute
  [#10](https://github.com/mantisbt-plugins/GaugeSupport/issues/10)
- Issue ranking page does not filter by severity
  [#23](https://github.com/mantisbt-plugins/GaugeSupport/issues/23)
- SQL error if config is not set
  [#28](https://github.com/mantisbt-plugins/GaugeSupport/issues/28)
- Missing license information
  [#31](https://github.com/mantisbt-plugins/GaugeSupport/issues/31)
- Removed duplicate language string
  [#33](https://github.com/mantisbt-plugins/GaugeSupport/issues/33)

### Security

- CSRF protection to forms
  [#16](https://github.com/mantisbt-plugins/GaugeSupport/issues/16)
- Prevent potential XSS attacks on Rankings page
  [#30](https://github.com/mantisbt-plugins/GaugeSupport/issues/30)


## [2.04] - 2019-01-20

### Added

- README file and screenshots

### Changed

- Print alert when there are no Rankings to display

### Fixed

- Check for Severity / Resolution to display Gauge form
- Invalid bug link in Rankings page


## [2.03] - 2018-10-11

### Fixed

- Support for MySQL 5.7
- Deprecated db_query_bound()
- Add missing check for Severity to display Gauge form

## [2.02] - 2017-09-17

### Added

- Initial release by cas with support for MantisBT 2.0

### Removed

- Support for MantisBT 1.x

---------

## [0.14] - 2011-01-??

### Fixed

- Various issues


## [0.1] - 2010-07-09

### Added

- Initial release by EvilRenegade


[Unreleased]: https://github.com/mantisbt-plugins/GaugeSupport/compare/v2.5.0...HEAD

[2.5.0]: https://github.com/mantisbt-plugins/GaugeSupport/compare/v2.04....v2.5.0
[2.04]: https://github.com/mantisbt-plugins/GaugeSupport/compare/v2.03...v2.04
[2.03]: https://github.com/mantisbt-plugins/GaugeSupport/compare/v2.02...v2.03
[2.02]: https://github.com/mantisbt-plugins/GaugeSupport/commit/v2.02

[0.14]: https://github.com/EvilRenegade/Gauge-Support/compare/2cc8f659a521278693eca10af5087dd74e680404...387fbed5c10f1be04e2ecc7d281f66dc3c81d560
[0.1]: https://github.com/EvilRenegade/Gauge-Support/commit/2cc8f659a521278693eca10af5087dd74e680404
