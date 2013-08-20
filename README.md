xqto-filemanager
================

Modifications to the Php File Manager at http://www.xqto.com

Setup
===============

- Copy files to an Apache server with Php configured
- Run `chmod 755 index.php` as an appropriate user. eg. apache:web.
- Update the username and password values in `index.php`

Note
---------------
- You may rename the main Php script `index.php`

History
===============
- Changed fault directory name validation check for ".". Added "/" check.
- Added a JavaScript confirmation popup when deleting files/folders.
- Merged the originally seperate 'styles.css' into 'index.php' to avoid including 'styles.css' in the do not show list.

License
===============

*GNU GENERAL PUBLIC LICENSE Version 3*



This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 3
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
