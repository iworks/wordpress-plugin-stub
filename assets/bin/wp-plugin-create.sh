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
#
# rename files: posttypes
#
PDIR=includes/iworks/posttypes
PCLASS=class-wordpress-plugin-stub-posttype
mv ${PDIR}/${PCLASS}.php ${PDIR}/class-${SLUG}-posttype.php
mv ${PDIR}/${PCLASS}-faq.php ${PDIR}/class-posttype-${SLUG}-faq.php
mv ${PDIR}/${PCLASS}-hero.php ${PDIR}/class-posttype-${SLUG}-hero.php
mv ${PDIR}/${PCLASS}-opinion.php ${PDIR}/class-posttype-${SLUG}-opinion.php
mv ${PDIR}/${PCLASS}-page.php ${PDIR}/class-posttype-${SLUG}-page.php
mv ${PDIR}/${PCLASS}-person.php ${PDIR}/class-posttype-${SLUG}-person.php
mv ${PDIR}/${PCLASS}-post.php ${PDIR}/class-posttype-${SLUG}-post.php
mv ${PDIR}/${PCLASS}-project.php ${PDIR}/class-posttype-${SLUG}-project.php
mv ${PDIR}/${PCLASS}-promotion.php ${PDIR}/class-posttype-${SLUG}-promotion.php
mv ${PDIR}/${PCLASS}-publication.php ${PDIR}/class-posttype-${SLUG}-publication.php
#
# language
#
mv languages/wordpress-plugin-stub.pot languages/${SLUG}.pot
xgettext -D languages/ -o ./languages/pl_PO.po


rm -rf ./.git ./assets/bin

echo git submodule add git@github.com:iworks/wordpress-options-class.git includes/iworks/options
echo git submodule add git@github.com:iworks/iworks-rate.git includes/iworks/rate
