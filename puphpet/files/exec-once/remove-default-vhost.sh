#!/usr/bin/env bash

file="/etc/apache2/sites-available/10-default_vhost_80.conf"
if [ -f "$file" ]
then
	rm -f /etc/apache2/sites-available/10-default_vhost_80.conf
	rm -f /etc/apache2/sites-enabled/10-default_vhost_80.conf
	/etc/init.d/apache2 restart
fi