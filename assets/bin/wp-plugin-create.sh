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
CLASS=${SLUG//-/_}
PREFIX=${CLASS^^}

echo Plugin class:  ${CLASS}
echo Plugin name:   ${NAME}
echo Plugin prefix: ${PREFIX}
echo Plugin slug:   ${SLUG}

git clone git@github.com:iworks/wordpress-plugin-stub.git ${SLUG}
cd ${SLUG}
#
# replace plugin name
#
FILES=$(find -type f|grep -E "txt|php|pot|json|Gruntfile.js")
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
# rename files
#
mv wordpress-plugin-stub.php ${SLUG}.php
mv includes/iworks/class-wordpress-plugin-stub.php includes/iworks/class-${SLUG}.php
mv includes/iworks/class-wordpress-plugin-stub-base.php includes/iworks/class-${SLUG}-base.php
mv includes/iworks/class-wordpress-plugin-stub-posttypes.php includes/iworks/class-${SLUG}-posttypes.php
mv languages/wordpress-plugin-stub.pot languages/${SLUG}.pot

rm -rf ./.git ./assets/bin

echo git submodule add git@github.com:iworks/wordpress-options-class.git includes/iworks/options
echo git submodule add git@github.com:iworks/iworks-rate.git includes/iworks/rate
