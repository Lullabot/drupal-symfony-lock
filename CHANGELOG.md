# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2018-07-11

### Changed

- Fixed checking the inverse result of wait() on locks #103

## [1.0.0] - 2018-05-04

### Added

- Adds a DrupalStore that implements Symfony's
  `Symfony\Component\Lock\StoreInterface`
- Support for extending a lock TTL #2
