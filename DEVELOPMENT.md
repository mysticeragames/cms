# Development

```bash
# Make sure no errors are found
php bin/console cache:clear

# Update
composer update

# Test
php bin/phpunit

# Make sure route priorities are correct (wildcards on the bottom)
php bin/console debug:router
```

## Locations

This project
- https://github.com/mysticeragames/markitstatic-cms

Packagist (for 'composer create-project ...')
- https://packagist.org/packages/mysticeragames/markitstatic-cms

Docker (TODO: create image with all dependencies)
- https://hub.docker.com/u/mysticeragames

## Resources

Markdown
- https://commonmark.thephpleague.com

Route priority
- https://symfony.com/doc/current/routing.html#priority-parameter

Testing
- https://symfony.com/doc/current/testing.html

Image processing

- https://glide.thephpleague.com
- https://imagemagick.org

FFMPEG

- https://www.ffmpeg.org

