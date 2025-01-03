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

# TODO: Allow submodules to be added within the CMS

# Add a repository to store content
git submodule add git@github.com:mysticeragames/mysticeragames.com-content.git content

# When it's an empty repository, make sure to have at least 1 commit
REPODIR=content
BRANCH=$(git -C $REPODIR branch --show-current)

if [ -z "$(git -C $REPODIR ls-files)" ]; then cp -r src/Demo/Content/* $REPODIR && git -C $REPODIR add . && git -C $REPODIR commit -m "initial" && git -C $REPODIR push -u origin $BRANCH; fi

# Add a repository to store generated files from deployment
git submodule add git@github.com:mysticeragames/mysticeragames.com-generated.git generated

# again: the same as content: make sure to have at least 1 commit
REPODIR=generated
BRANCH=$(git -C $REPODIR branch --show-current)
if [ -z "$(git -C $REPODIR ls-files)" ]; then cp -r src/Demo/Generated/* $REPODIR && git -C $REPODIR add . && git -C $REPODIR commit -m "initial" && git -C $REPODIR push -u origin $BRANCH; fi


symfony server:start

```

### Run in Docker (TODO)

No volumes needed: the content + generated files are attached as git-submodule repositories, so the docker container can be destroyed at any time.

```bash
docker run -d --name -p 8585:80 markitstatic-cms mysticeragames/markitstatic-cms
```

http://localhost:8585
