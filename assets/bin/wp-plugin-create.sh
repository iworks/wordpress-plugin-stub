#!/usr/bin/env bash

if [ -z "$1" ]
then
    echo "No argument supplied"
    echo "usage $0 'Plugin Name'"
    exit
fi

NAME=${1}
SLUG=${NAME// /-}
SLUG=${SLUG,,}
if [ "$2" ]
then
    SLUG=${2}
    SLUG=${SLUG,,}
fi
CLASS=${SLUG//-/_}
PREFIX=${CLASS^^}
PACKAGE=$(echo ${CLASS} | perl -pe 's/([a-zA-Z]+)/(ucfirst $1)/ge')
YEAR=$(date +%Y)

echo Plugin class:  ${CLASS}
echo Plugin name:   ${NAME}
echo Plugin prefix: ${PREFIX}
echo Plugin package: ${PACKAGE}
echo Plugin slug:   ${SLUG}

#
# clone repository
#
git clone git@github.com:iworks/wordpress-plugin-stub.git ${SLUG}
cd ${SLUG}

#
# prepare files
#
FILES=$( \
    find -type f \
    | grep -E "(txt|php|pot|json|js|mjs|md|CHANGELOG|gitignore)$" \
    | grep -v "assets/externals" \
    | grep -v ".git/" \
    | grep -v "node_modules"\
)
#
# update copyright year
#
perl -pi -e "s/CURRENT_YEAR/${YEAR}/g"   ${FILES}
#
# replace plugin name
#
perl -pi -e "s/wordpress-plugin-stub/${SLUG}/g"   ${FILES}
perl -pi -e "s/WORDPRESS_PLUGIN_STUB/${PREFIX}/g" ${FILES}
perl -pi -e "s/wordpress_plugin_stub/${CLASS}/g"  ${FILES}
perl -pi -e "s/WordPress Plugin Stub/${NAME}/g"   ${FILES}
perl -pi -e "s/WordPress_Plugin_Stub/${PACKAGE}/g"   ${FILES}
perl -pi -e "s/IWorks\\\\WordPressPluginStub/${PACKAGE}/g"   ${FILES}
#
# docs - drop stub-only fragments (assets/bin is removed below)
#
perl -0pi -e 's/## Scaffolding[^\n]*\n.*?\n\n(?=## )//s' .opencode/docs/build.md
#
# docs - AGENTS.md: describe the generated plugin, not the stub
#
export GENERATED_PLUGIN_NAME=${NAME}
perl -0pi -e 's/## What the project is\n\n.*?(?=\n\n## )/"## What the project is\n\nThe \"$ENV{GENERATED_PLUGIN_NAME}\" WordPress plugin:\nmain file + PHP classes (`\/includes\/iworks`) + options config (`\/etc\/options.php`)\n+ assets (`\/assets`), built with a bun bundler (`\/bundler` \x{2192} `release\/` ZIPs);\nsources contain build-time placeholders (`PLUGIN_VERSION`, `CURRENT_YEAR`, \x{2026})"/se' AGENTS.md
#
# make dirs
#
mkdir -p ./assets/scripts/src/admin
mkdir -p ./assets/sass/admin
mkdir -p ./assets/styles/admin
mkdir -p ./includes/iworks
#
# rename plugin file
#
mv wordpress-plugin-stub.php ${SLUG}.php
#
# rename includes files
#
PCLASS=class-iworks-wordpress-plugin-stub
mv includes/iworks/${PCLASS}.php includes/iworks/class-iworks-${SLUG}.php
mv includes/iworks/${PCLASS}-base.php includes/iworks/class-iworks-${SLUG}-base.php
#
# rename includes files: classes
#
PDIR=includes/iworks/wordpress-plugin-stub
NAMES=("cron" "db" "github" "posttypes" "wp-admin")
for name in "${NAMES[@]}"; do
    mv ${PDIR}/${PCLASS}-${name}.php ${PDIR}/class-iworks-${SLUG}-${name}.php
done
#
# rename files: posttypes
#
PDIR=includes/iworks/wordpress-plugin-stub/posttypes
NAMES=("faq" "hero" "opinion" "page" "person" "post" "project" "featured" "publication" "testimonials")
mv ${PDIR}/${PCLASS}-posttype.php ${PDIR}/class-iworks-${SLUG}-posttype.php
for name in "${NAMES[@]}"; do
    mv ${PDIR}/${PCLASS}-posttype-${name}.php ${PDIR}/class-iworks-${SLUG}-posttype-${name}.php
done
#
# rename directory
#
mv includes/iworks/wordpress-plugin-stub includes/iworks/${SLUG}
#
# language
#
mv languages/wordpress-plugin-stub.pot languages/${SLUG}.pot
xgettext -D languages/ -o ./languages/pl_PO.po
#
# remove unnecessary files
#
rm -rf ./.git ./assets/bin
rm -f bun.lock bun.lockb
#
# submodules are empty dirs after a fresh clone - install-libs fills them
#
rm -rf includes/iworks/options includes/iworks/rate
#
# submodules - install with bun
#
echo "next: bun run install-libs https all && bun install && bun run build"
