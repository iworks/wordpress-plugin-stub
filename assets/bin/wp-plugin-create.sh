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
mv includes/iworks/class-wordpress-plugin-stub.php includes/iworks/class-${SLUG}.php
mv includes/iworks/class-wordpress-plugin-stub-base.php includes/iworks/class-${SLUG}-base.php
#
# rename includes files: classes
#
PDIR=includes/iworks/wordpress-plugin-stub
PCLASS=class-iworks-wordpress-plugin-stub
NAMES=("cron" "github" "posttypes" "wp-admin")
for name in "${NAMES[@]}"; do
    mv ${PDIR}/${PCLASS}-${name}.php ${PDIR}/${PCLASS}-${SLUG}-${name}.php
done
#
# rename files: posttypes
#
PDIR=includes/iworks/wordpress-plugin-stub/posttypes
PCLASS=class-wordpress-plugin-stub-posttype
NAMES=("faq" "hero" "opinion" "page" "person" "post" "project" "promotion" "publication" "testimonials")
mv ${PDIR}/${PCLASS}.php ${PDIR}/class-${SLUG}-posttype.php
for name in "${NAMES[@]}"; do
    mv ${PDIR}/${PCLASS}-${name}.php ${PDIR}/class-${SLUG}-posttype-${name}.php
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


rm -rf ./.git ./assets/bin

echo git submodule add git@github.com:iworks/wordpress-options-class.git includes/iworks/options
echo git submodule add git@github.com:iworks/iworks-rate.git includes/iworks/rate
