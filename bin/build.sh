#!/bin/bash
# ----------------------------------------------------------
# build.sh
#
# usage:  ./build.sh
#
# For packaging the extension into a downloadable zip.
#
#    Corey Snipes <corey@twomile.com>
#    November 3, 2010
#
# ----------------------------------------------------------

BASE_DIR=../
VERSION=`cat ../version`

# set build params
FILES_DIR=$BASE_DIR/www/system
DOCS_DIR=$BASE_DIR/doc
ZIP=redirect_after_login-2.x-$VERSION.zip
BUILD_DIR=../build

# display status
echo "Packaging component to: $ZIP"

# build
[ -d $BUILD_DIR ] || mkdir -p $BUILD_DIR
cd $BUILD_DIR
rm *.zip
cp -pr $FILES_DIR .
find . -type f -name ".DS_Store" | xargs rm -f
cp -p $DOCS_DIR/*.txt .
zip -r $ZIP *

# remove build files
rm -rf *.txt system

# return home
cd -

# display status
echo "Done."

exit 0
