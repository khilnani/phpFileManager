xqto-filemanager
================

_An heavily modified version of the Php File Manager at http://www.xqto.com_

Setup
===============

- Copy files to a dedicated directory (eg. `admin\`) on an Apache server with Php configured.
- Run `chmod 755 index.php`.
- Update configuration properties in `config.ini`.
- Chmod the Base Content Directory to be writable.

Notes
===============
- Image to Base64 - http://webcodertools.com/imagetobase64converter/Create
- Base64 to Image - http://www.freeformatter.com/base64-encoder.html

Screenshots
=========

<img src="https://raw.github.com/khilnani/xqto-filemanager/master/screenshots/File%20Upload.png" />
<img src="https://raw.github.com/khilnani/xqto-filemanager/master/screenshots/Deletion%20Error.png" />
<img src="https://raw.github.com/khilnani/xqto-filemanager/master/screenshots/Deletion%20Success.png" />
<img src="https://raw.github.com/khilnani/xqto-filemanager/master/screenshots/Folder%20Creation.png" />
<img src="https://raw.github.com/khilnani/xqto-filemanager/master/screenshots/Unzip.png" />
<img src="https://raw.github.com/khilnani/xqto-filemanager/master/screenshots/File%20Edit.png" />

History
===============
- NEW: Externalized configuration options to `config.ini`.
- NEW: Added ability to customize the base content directory that will be  managed.
- UPDATE: Improvement general formattig.
- NEW: Added capability to unzip uploaded Zip files.
- UPDATE: Improved Error messaging. Added Success messaging.
- UPDATE: Converted images to base64 and embedded in index.php.
- UPDATE: Improved directory and file name validation as well as case sensitivity.
- NEW: Added a JavaScript confirmation popup when unzipping, deleting files/folders.

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
