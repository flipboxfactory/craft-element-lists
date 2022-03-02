Changelog
=========

## 3.3.0 - 2022-01-27
### Changed
- Element index controller disables database query cache.

## 3.1.3 - 2021-03-23
### Updated
- Opened `tightenco/collect` dependency up to >=5.5

## 3.1.2 - 2021-01-12
### Fixed
- Sorting by field's sort order would throw an error within Matrix blocks.

## 3.1.1 - 2021-01-05
### Changed
- Lists nested inside Matrix blocks would return incorrect relations.

## 3.1.0 - 2020-07-14
### Changed
- Adding support for Craft 3.4

## 3.0.3 - 2021-01-12
### Fixed
- Sorting by field's sort order would throw an error within Matrix blocks.

## 3.0.2 - 2021-01-05
### Fixed
- Lists nested inside Matrix blocks would return incorrect relations.

## 3.0.1 - 2019-12-30
### Changed
- Plugin variable `$category` is publicly accessible.

## 3.0.0 - 2019-10-09
### Changed
- Field value is now a `RelationshipInterface` and not the default `ElementQueryInterface`.  Note: this may introduce 
breaking changes!

## 2.2.1 - 2019-09-24
### Fixed
- Fixing issue w/ relation alias used in Craft 3.2.x

## 2.2.0 - 2019-05-03
### Added
- Fields can disable sort order enforcement.  We recommend disabling this when field relations are in the thousands.

## 2.1.2 - 2019-04-30
### Changed
- Index sources are limited to the input sources defined via settings. [#3](https://github.com/flipboxfactory/craft-element-lists/issues/3)

## 2.1.1.1 - 2019-04-29
### Fixed
- Optimized query which was returning multiple of the same element.

## 2.1.1 - 2019-04-28
### Fixed
- Query::distinct would throw an error on postgres databases [#2](https://github.com/flipboxfactory/craft-element-lists/issues/2)

## 2.1.0 - 2019-04-25
### Changed
- Relations are stored in the native 'relations' table resulting in seamless switching between field types.

### Fixed
- Various multi-size related experiences.

## 2.0.1 - 2019-04-12
### Added
- Returning an error json if saving an association errors.

## 2.0.0 - 2019-03-26
### Changed
- Namespacing from `flipbox\element\list\*` to `flipbox\craft\element\list\*`

## 1.0.2 - 2018-10-08
### Added
- `flipbox\craft\element\lists\fields\ElementSourceList::$ignoreSearchKeywords` param to ignore recursively trying to generate keywords 

## 1.0.1 - 2018-09-20
### Added
- `flipbox\craft\element\lists\db\SourceUserElementQuery` to build a full query for users

### Changed
- Fields can provide their own query class

## 1.0.0 - 2018-04-30
- Initial release
