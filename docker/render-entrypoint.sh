#!/bin/sh
set -eu

# Render supplies PORT at runtime (10000 by default). Apache's stock image is
# configured for port 80, so update both listen directives before it starts.
PORT="${PORT:-10000}"

sed -i "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
sed -i 's!DocumentRoot /var/www/html!DocumentRoot /var/www/html/public!' /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
