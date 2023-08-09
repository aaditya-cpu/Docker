#!/bin/bash


# Create files
touch member-management-plugin.php
touch readme.txt

# Create folders
mkdir assets
mkdir assets/css
mkdir assets/js
mkdir assets/images
mkdir includes
mkdir admin

# Create files within folders
touch includes/functions.php
touch includes/shortcode.php
touch includes/post-type.php
touch admin/settings.php

# Print success message
echo "Plugin files and folders created successfully!"
