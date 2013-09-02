#!/usr/bin/env sh

echo "Checking if the plugin is installed..."
if test -f app/etc/modules/Hull_Connection.xml
then
  echo "The plugin seems already installed."
else
  echo "Plugin not installed. Installing..."
  curl -kL https://github.com/hull/hull-magento/archive/master.tar.gz | tar zx --strip=1
fi

echo "Checking if the library is installed..."
if test -d lib/Hull
then
  echo "The library seems already installed."
else
  echo "Library not installed. Installing..."
  mkdir -p lib
  curl -kL https://github.com/hull/hull-php/archive/master.tar.gz | tar zx --strip=1 -C lib
fi

echo "Installation complete!"

