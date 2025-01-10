# MakeItStatic-Docker

## Build locally

```bash
# cms-base
docker build -t test:base ./.docker/cms-base
docker run --rm test:base cat /etc/cms-base-version

# cms
docker build -t test:cms --build-arg BASE_IMAGE=test --build-arg BASE_VERSION=base -f ./.docker/cms/Dockerfile .
docker run --rm test:cms cat /etc/cms-base-version
docker run --rm test:cms cat /etc/cms-version
docker run --rm test:cms bin/phpunit
```
