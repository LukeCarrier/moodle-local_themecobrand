# This file is part of Moodle - http://moodle.org/
#
# Moodle is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Moodle is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

#
# Theme co-branding.
#
# Enables customised per-organisation theming for TotaraLMS.
#
# @author Luke Carrier <luke@tdm.co>
# @copyright (c) The Development Manager Ltd
# @license GPL v3
#

.PHONY: all clean

TOP := $(dir $(CURDIR)/$(word $(words $(MAKEFILE_LIST)), $(MAKEFILE_LIST)))

all: build/local_themecobrand.zip

clean:
	rm -rf $(TOP)build

build/local_themecobrand.zip:
	mkdir -p $(TOP)build
	cp -rv $(TOP)src       $(TOP)build/themecobrand
	cp     $(TOP)README.md $(TOP)build/themecobrand/README.txt
	cp -rv $(TOP)patches   $(TOP)build/themecobrand/patches
	cd $(TOP)build \
		&& zip -r local_themecobrand.zip themecobrand
	rm -rfv $(TOP)build/themecobrand
