#!/bin/bash
if [ -d "$1" ]; then
	echo "Error! $1 exists!"
else
	mkdir -p {$1/assets,$1/class,$1/interface,$1/process,$1/widgets}
	touch $1/module.php
	echo "Done!"
	ls -lah $1
fi
