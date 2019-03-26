# Element List Field Type for Craft CMS
[![Join the chat at https://gitter.im/flipboxfactory/craft-element-lists](https://badges.gitter.im/flipboxfactory/craft-element-lists.svg)](https://gitter.im/flipboxfactory/craft-element-lists?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Software License](https://img.shields.io/badge/license-Proprietary-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/flipboxfactory/craft-element-lists/master.svg?style=flat-square)](https://travis-ci.com/flipboxfactory/craft-element-lists)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/flipboxfactory/craft-element-lists.svg?style=flat-square)](https://scrutinizer-ci.com/g/flipboxfactory/craft-element-lists/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/flipboxfactory/craft-element-lists.svg?style=flat-square)](https://scrutinizer-ci.com/g/flipboxfactory/craft-element-lists)
[![Total Downloads](https://img.shields.io/packagist/dt/flipboxfactory/craft-element-lists.svg?style=flat-square)](https://packagist.org/packages/flipboxfactory/craft-element-lists)

Element List introduces a table-based element relational field type; perfect for large or growing element relations.  

![Screenshot](resources/screenshots/field.png)

## Requirements
This plugin requires Craft CMS 3.0 or later.

## Installation
Choose one of the following ways to add [Element List] to your project:

1. Composer:

    Simply run the following command from your project root:

    ```
    composer require flipboxfactory/craft-element-lists
    ```

2. Craft CMS Plugin Store:

    Within your Craft CMS project admin panel, navigate to the '[Plugin Store]' and search for '[Element List]'. Installation is a button click away.


Once the plugin is included in your project, navigate to the Control Panel, go to Settings → Plugins and click the “Install” button for [Element List].

Additional information (including pricing) can be found in the [Plugin Store].


## Features
[Element List] brings the familiar element 'index' view to your fields.  Built for large relation sets, [Element List] provides content publishers a filterable, table based view to related elements.  [Element List] is as easy to configure as native relationships. 

Here are some of the features at a glance:
* Add/Remove relations in real time
* Handles large relational sets (hundreds/thousands)
* Sortable list view
* Searchable list view
* Developer friendly

### Templating
Similar to [native relational fields](https://docs.craftcms.com/v3/relations.html), an [Element List] field returns an [Element Query Interface].  

Loop through field relations:
```twig
<ul>
    {% for element in element.fieldHandle.all() %}
        <li>{{ element.id }} - {{ element.customField }}</li>
    {% endfor %}
</ul>
```

Element Lists also supports eager-loading.  Simply follow the native [nested sets eager loading documentation](https://docs.craftcms.com/v3/dev/eager-loading-elements.html#eager-loading-nested-sets-of-elements).


### Screenshots
![Element Source Filter](resources/screenshots/input-source-filter.png)

![Inline Element Edit](resources/screenshots/input-inline-element-edit.png)

![Select Modal](resources/screenshots/input-select-modal.png)

![Input Remove Action](resources/screenshots/input-remove-action.png)

![Field Settings](resources/screenshots/field-settings.png)


## Credits
- [Flipbox Digital](https://github.com/flipbox)

[Element Query Interface]: https://docs.craftcms.com/v3/dev/element-queries/#executing-element-queries
[Plugin Store]: https://plugins.craftcms.com/
[Element List]: https://plugins.craftcms.com/
