# **MakeIt***Static*-CMS

Flat-File CMS with Static Site Generator

[![GitHub Release](https://img.shields.io/github/v/release/mysticeragames/MakeItStatic-CMS?sort=semver&label=Release)](https://github.com/mysticeragames/MakeItStatic-CMS/releases/latest)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/mysticeragames/MakeItStatic-CMS/trigger.release.yml?label=Docker%20Release)](https://github.com/mysticeragames/MakeItStatic-CMS/actions/workflows/trigger.release.yml)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/mysticeragames/MakeItStatic-CMS/trigger.main.yml?branch=main&label=Docker%20Development%20(dev-main))](https://github.com/mysticeragames/MakeItStatic-CMS/actions/workflows/trigger.main.yml)
[![Docker Pulls](https://img.shields.io/docker/pulls/mysticeragames/makeitstatic-cms?label=Docker%20Pulls)](https://hub.docker.com/r/mysticeragames/makeitstatic-cms)

## *WORK IN PROGRESS!*

# Docker

```bash
# Create volume
docker volume create makeitstatic-cms-content

# Pull latest version
docker pull mysticeragames/makeitstatic-cms:latest

# Run the CMS
docker run -d -v makeitstatic-cms-content:/var/www/html/content --name makeitstatic-cms --restart unless-stopped -p 8000:8080 mysticeragames/makeitstatic-cms:latest

# Show status
docker ps -a -f "name=makeitstatic-cms"

# Show logs
docker exec makeitstatic-cms tail /var/log/nginx/project_error.log -n 5
docker exec makeitstatic-cms tail /var/log/nginx/project_access.log -n 5

# Stop container
docker stop makeitstatic-cms

# Remove container
docker rm makeitstatic-cms --force

# List volume
docker volume ls -f name=makeitstatic-cms-content

# Inspect volume
docker run --rm -v makeitstatic-cms-content:/app:ro alpine:latest find /app -type f

# Backup named volume to my_backup.tar
docker run --rm -u $(id -u):$(id -g) -v makeitstatic-cms-content:/vol -v $(pwd):/app alpine:latest tar c -f /app/my_backup.tar -C /vol .

# Restore named volume from my_backup.tar
docker run --rm -u 0:0 -v makeitstatic-cms-content:/vol -v $(pwd):/app alpine:latest tar x -f /app/my_backup.tar -C /vol .
```

- http://localhost:8000

### Update

```bash
docker rm makeitstatic-cms --force

docker run --pull always -d --name makeitstatic-cms --restart unless-stopped -p 8000:8080 mysticeragames/makeitstatic-cms:latest
```

### TEMP...

```bash
# Add a repository to store content
docker exec makeitstatic-cms sh -c 'REPO_CONTENT=https://github.com/mysticeragames/mysticeragames.com-content.git && git submodule add --force $REPO_CONTENT content && git -C content log --oneline -1 || ( echo "no commits yet" && cp -r ./src/Demo/Content/Minimal/* ./content && git -C content add . && git -C content commit -m "initial" && git -C content push -u origin $(git -C content branch --show-current) && rm -r content && git submodule add --force $REPO_CONTENT content );'

# Add a repository to store generated files from deployment
docker exec makeitstatic-cms sh -c 'REPO_DEPLOY=https://github.com/mysticeragames/mysticeragames.com-generated.git && git submodule add --force $REPO_DEPLOY generated && git -C generated log --oneline -1 || ( echo "no commits yet" && cp -r src/Demo/Generated/* generated && git -C generated add . && git -C generated commit -m "initial" && git -C generated push -u origin $(git -C generated branch --show-current) && rm -r generated && git submodule add --force $REPO_DEPLOY generated )'
```

### Requirements

- Docker
- A git repository to store content
- A git repository to store generated files

### Features

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
composer create-project mysticeragames/makeitstatic-cms makeitstatic-cms "0.1.*"

cd makeitstatic-cms

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

### Run in Docker

Idea/TODO: The content + generated files are attached as git-submodule repositories, so the docker container can be destroyed at any time.

```bash
# Normal usage
docker pull mysticeragames/makeitstatic-cms:latest  # pull latest version
docker run -d --name cms --restart unless-stopped -p 8000:8080 mysticeragames/makeitstatic-cms:latest  # start container
docker exec cms sh -c "mkdir -p ./content && cp -r ./src/Demo/Content/Full/* ./content" # Copy demo content
docker exec -t cms php bin/console site # CMS commands
docker stop cms  # stop container
docker start cms  # start container again
docker rm cms --force  # remove container

# Other
docker run --rm -it mysticeragames/makeitstatic-cms:latest php -v
docker run --rm -it mysticeragames/makeitstatic-cms:latest composer -v
docker run --rm -it mysticeragames/makeitstatic-cms:latest npm -v
docker run --rm -it mysticeragames/makeitstatic-cms:latest sh

# Debug run
docker run --rm --name cms -p 8000:8080 mysticeragames/makeitstatic-cms:latest


docker exec cms sh -c 'echo -e "APP_ENV=dev\nAPP_SECRET=" > /var/www/html/.env.local && php bin/console cache:clear && /usr/sbin/nginx -s reload' # set env to dev

docker exec cms sh -c 'echo -e "APP_ENV=prod\nAPP_SECRET=" > /var/www/html/.env.local && php bin/console cache:clear && /usr/sbin/nginx -s reload' # set env to prod


# View latest 5 log lines:
docker exec cms tail /var/log/nginx/project_access.log -n 5
docker exec cms tail /var/log/nginx/project_error.log -n 5
```

- http://localhost:8000
- http://localhost:8000/---cms

```bash
# Build new image
docker build --no-cache -t mysticeragames/makeitstatic-cms:latest .
docker build -t mysticeragames/makeitstatic-cms:latest .
# docker push mysticeragames/makeitstatic-cms:latest   # push to repo (TODO: make Github Action that takes the Release version as tag)

# DEVELOPMENT MODE: Mount local folder (including all the CMS files - note: use APP_ENV=prod to avoid messages)
docker run --rm --name makeitstatic-cms -p 8000:8080 -v $(pwd):/var/www/html mysticeragames/makeitstatic-cms:latest


### TODO / idea:
docker exec cms php bin/console site:add-git-source {repo}
### TODO / idea:
docker exec cms php bin/console site:add-git-target {repo}

# But for now, to add GIT repo's (content + generated)

# Add a repository to store content
docker exec makeitstatic-cms sh -c 'REPO_CONTENT=https://github.com/mysticeragames/mysticeragames.com-content.git && git submodule add --force $REPO_CONTENT content && git -C content log --oneline -1 || ( echo "no commits yet" && cp -r ./src/Demo/Content/Minimal/* ./content && git -C content add . && git -C content commit -m "initial" && git -C content push -u origin $(git -C content branch --show-current) && rm -r content && git submodule add --force $REPO_CONTENT content );'

# Add a repository to store generated files from deployment
docker exec makeitstatic-cms sh -c 'REPO_DEPLOY=https://github.com/mysticeragames/mysticeragames.com-generated.git && git submodule add --force $REPO_DEPLOY generated && git -C generated log --oneline -1 || ( echo "no commits yet" && cp -r src/Demo/Generated/* generated && git -C generated add . && git -C generated commit -m "initial" && git -C generated push -u origin $(git -C generated branch --show-current) && rm -r generated && git submodule add --force $REPO_DEPLOY generated )'

# TODO: Mount SSH GIT key to container (or: possibility to upload SSH key)
/home/makeitstatic/.ssh/id_.... (note: permissions...)

```
