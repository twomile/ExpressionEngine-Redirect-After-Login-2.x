#!/bin/bash
# ----------------------------------------------------------
# build.sh
#
# usage:  ./build.sh
#
# For packaging the extension into a downloadable zip.
#
#    Corey Snipes <corey@twomile.com>
#    October 27, 2011
#
# ----------------------------------------------------------

BASE_DIR=../
VERSION=`cat ../version`
ADDON_DIRNAME=twomile_login_redirect

# set build params
FILES_DIR=$BASE_DIR/src
DOCS_DIR=$BASE_DIR/doc
ZIP=redirect_after_login-2.x-$VERSION.zip
BUILD_DIR=../build

# display status
echo "Packaging component to: $ZIP"

# build
[ -d $BUILD_DIR ] || mkdir -p $BUILD_DIR
[ -d $BUILD_DIR/$ADDON_DIRNAME ] || mkdir -p $BUILD_DIR/$ADDON_DIRNAME
cd $BUILD_DIR
rm *.zip
cp -pr $FILES_DIR/ ./$ADDON_DIRNAME
find . -type f -name ".DS_Store" | xargs rm -f
cp -p $DOCS_DIR/*.txt .
zip -r $ZIP *

# remove build files
rm -rf *.txt $ADDON_DIRNAME

# return home
cd -

# display status
echo "Done."

exit 0
