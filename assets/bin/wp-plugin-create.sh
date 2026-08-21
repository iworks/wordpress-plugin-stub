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
YEAR=$(date +%Y)

echo Plugin class:  ${CLASS}
echo Plugin name:   ${NAME}
echo Plugin prefix: ${PREFIX}
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
    | grep -E "(txt|php|pot|json|js|md|CHANGELOG|gitignore)$" \
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
#
# make dirs
#
mkdir -p ./assets/scripts/admin/src
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
mv includes/iworks/${PCLASS}.php includes/iworks/class-${SLUG}.php
mv includes/iworks/${PCLASS}-base.php includes/iworks/class-${SLUG}-base.php
#
# rename includes files: classes
#
PDIR=includes/iworks/wordpress-plugin-stub
NAMES=("cron" "github" "posttypes" "wp-admin")
for name in "${NAMES[@]}"; do
    mv ${PDIR}/${PCLASS}-${name}.php ${PDIR}/class-${SLUG}-${name}.php
done
#
# rename files: posttypes
#
PDIR=includes/iworks/wordpress-plugin-stub/posttypes
NAMES=("faq" "hero" "opinion" "page" "person" "post" "project" "featured" "publication" "testimonials")
mv ${PDIR}/${PCLASS}-posttype.php ${PDIR}/class-${SLUG}-posttype.php
for name in "${NAMES[@]}"; do
    mv ${PDIR}/${PCLASS}-posttype-${name}.php ${PDIR}/class-${SLUG}-posttype-${name}.php
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
#
# submodules - show commands
#
echo git submodule add git@github.com:iworks/wordpress-options-class.git includes/iworks/options
echo git submodule add git@github.com:iworks/iworks-rate.git includes/iworks/rate
