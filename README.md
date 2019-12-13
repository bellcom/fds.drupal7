# Drupal 7 [Fælles design system](https://designsystem.dk) base theme presentation repo.

Composer based Drupal 7 project

## How to intstall

```
git clone git@github.com:bellcom/fds.drupal7.git
cd fds.drupal7
git submodule init
git submodule update
composer install
```

Theme git sumodule will be symlinked to `public_html/sites/all/themes/contrib` folder.

## Theme development
[Fds base theme git repository](https://github.com/bellcom/fds_base_theme)
is attached to this project as git submodule.

You can do you development inside git submodule.

Note: if you are updating git submodule reference it may affect environment
where this project is used as fds base theme demo.
