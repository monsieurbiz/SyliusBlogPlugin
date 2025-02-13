<h1 align="center">Sylius Blog Plugin</h1>

[![Blog Plugin license](https://img.shields.io/github/license/monsieurbiz/SyliusBlogPlugin?public)](https://github.com/monsieurbiz/SyliusBlogPlugin/blob/master/LICENSE.txt)
[![Tests Status](https://img.shields.io/github/actions/workflow/status/monsieurbiz/SyliusBlogPlugin/tests.yaml?branch=master&logo=github)](https://github.com/monsieurbiz/SyliusBlogPlugin/actions?query=workflow%3ATests)
[![Recipe Status](https://img.shields.io/github/actions/workflow/status/monsieurbiz/SyliusBlogPlugin/recipe.yaml?branch=master&label=recipes&logo=github)](https://github.com/monsieurbiz/SyliusBlogPlugin/actions?query=workflow%3ASecurity)
[![Security Status](https://img.shields.io/github/actions/workflow/status/monsieurbiz/SyliusBlogPlugin/security.yaml?branch=master&label=security&logo=github)](https://github.com/monsieurbiz/SyliusBlogPlugin/actions?query=workflow%3ASecurity)

This plugin adds a blog to your Sylius project. It allows you to create blog articles, tags and authors.

## Compatibility

## Compatibility

| Sylius Version | PHP Version     |
|----------------|-----------------|
| 1.12           | 8.1 - 8.2 - 8.3 |
| 1.13           | 8.1 - 8.2 - 8.3 |
| 1.14           | 8.1 - 8.2 - 8.3 |

## Installation

If you want to use our recipes, you can add recipes endpoints to your composer.json by running this command:

```bash
composer config --no-plugins --json extra.symfony.endpoint '["https://api.github.com/repos/monsieurbiz/symfony-recipes/contents/index.json?ref=flex/master","flex://defaults"]'
```

Install the plugin via composer:

```bash
composer require monsieurbiz/sylius-blog-plugin:dev-master
```

<!-- The section on the flex recipe will be displayed when the flex recipe will be available on contrib repo
<details><summary>For the installation without flex, follow these additional steps</summary>
-->

Change your `config/bundles.php` file to add this line for the plugin declaration:

```php
<?php

return [
    //..
    MonsieurBiz\SyliusBlogPlugin\MonsieurBizSyliusBlogPlugin::class => ['all' => true],
];
```

Add the plugin's routing by creating a new file in `config/packages/monsieurbiz_sylius_blog_plugin.yaml` with the following content:

```yaml
imports:
    resource: '@MonsieurBizSyliusBlogPlugin/Resources/config/config.yaml'
```

Add the plugin's routing by creating a new file in `config/routes/monsieurbiz_sylius_blog_plugin.yaml` with the following content:

```yaml
monsieurbiz_blog_plugin:
   resource: '@MonsieurBizSyliusBlogPlugin/Resources/config/routes.yaml'
```

And finally, update your database:

```bash
bin/console doctrine:migrations:migrate
```


## License

This plugin is under the MIT license.
Please see the [LICENSE](LICENSE) file for more information._
