# **MarkIt***Static*-CMS

## *WORK IN PROGRESS!*

#### No database | Static content files | Static generated files | S3 connection for media

Requirements:

- GIT - https://git-scm.com/downloads (todo: put in docker image)
- composer - https://getcomposer.org/download/ (todo: put in docker image)
- Symfony CLI - https://symfony.com/download (todo: put in docker image)
- PHP - https://www.php.net/downloads.php (todo: put in docker image)
- FFMPEG - https://www.ffmpeg.org/download.html (to extract frame for video-poster) (todo: put in docker image)
- TODO: Docker - https://docs.docker.com/engine/install/ubuntu/

Features:

- Content in it's own repository
- Generated files in it's own repository
- Store media files in an S3 bucket (because large files should not be stored in GIT)
- CMS directory can be removed at any time, quickly spin up a new updated CMS
- Markdown content creation
- Variables in markdown (Front Matter)
- Twig template system ( https://twig.symfony.com )
- Save = git push to content-repository
- Deploy = git push to generated-repository (setup Cloudflare Pages, or any other CI-tool to deploy those files to the cloud)

## Install locally

```bash
composer create-project mysticeragames/markitstatic-cms markitstatic-cms "0.1.*"

cd markitstatic-cms


# TODO: Connect git repositories from within the CMS


REPO_CONTENT=git@github.com:mysticeragames/mysticeragames.com-content.git
REPO_DEPLOY=git@github.com:mysticeragames/mysticeragames.com-generated.git

# Add a repository to store content
git submodule add --force $REPO_CONTENT content && git -C content log --oneline -1 || ( echo "no commits yet" && cp -r src/Demo/Content/Minimal/* content && git -C content add . && git -C content commit -m "initial" && git -C content push -u origin $(git -C content branch --show-current) && rm -r content && git submodule add --force $REPO_CONTENT content );

# Add a repository to store generated files from deployment
git submodule add --force $REPO_DEPLOY generated && git -C generated log --oneline -1 || ( echo "no commits yet" && cp -r src/Demo/Generated/* generated && git -C generated add . && git -C generated commit -m "initial" && git -C generated push -u origin $(git -C generated branch --show-current) && rm -r generated && git submodule add --force $REPO_DEPLOY generated )


symfony server:start
```

|  |  |
| ---- | --- |
| Website   | http://localhost:8000 |
| CMS       | http://localhost:8000/---cms |

```bash
php bin/console site
php bin/console site:generate # Generate static files
php bin/console site:deploy # Add/commit/push all generated files to the connected repository




# Howto list submodules
git submodule

# Howto pull submodule
# TODO: first stash local changes.
git -C content pull
git -C generated pull

# Howto remove submodules:
git rm -rf content --force; rm -rf content; rm -rf .git/modules/content
git rm -rf generated --force; rm -rf generated; rm -rf .git/modules/generated


# TODO: CHECK stash / avoid merge conflicts:
https://stackoverflow.com/a/76212621/2263114

```

### Run in Docker (TODO)

No volumes needed: the content + generated files are attached as git-submodule repositories, so the docker container can be destroyed at any time.

```bash
docker run -d --name -p 8585:80 markitstatic-cms mysticeragames/markitstatic-cms
```

http://localhost:8585
