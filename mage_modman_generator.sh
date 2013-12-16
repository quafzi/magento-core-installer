#!/bin/sh
find -type f | sed 's/\.\/\(.*\)/\1 \1/' | grep -v '^.git' > modman
