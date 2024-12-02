#!/usr/bin/env bash

set -e  # Exit on error

# Clean build directory
rm -rf build

# Prepare files to upload
rsync -avh . ./build --exclude-from '.exclude-list' --delete

# Change to build directory
cd build

# Checkout the SVN repo
svn co -q "https://plugins.svn.wordpress.org/woo-poly-integration" svn

# Create trunk directory
mkdir -p svn/trunk

# Copy our new version of the plugin into trunk
rsync -r -p ./* svn/trunk

# Add new files to SVN
svn stat svn | grep '^?' | awk '{print $2}' | xargs -I x svn add x@

# Remove deleted files from SVN
svn stat svn | grep '^!' | awk '{print $2}' | xargs -I x svn rm --force x@

# Show status
svn stat svn

# Commit to SVN
svn ci --no-auth-cache --username "$WP_ORG_USERNAME" --password "$WP_ORG_PASSWORD" svn -m "Update to version $TRAVIS_TAG with PHP 8.4 support"
