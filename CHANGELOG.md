Changelog
=========

## 2.1.1.1 - 2019-04-28
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
