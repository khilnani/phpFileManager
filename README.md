_phpFileManager_
================

A web based file manager with the features below built using Php

- Password/authentication. _Can be configured to be enabled/disabled._
- File Upload. _Can be configured to be enabled/disabled._
- Ability to unzip ZIP files to a desired folder.
- Folder creation. _Can be configured to be enabled/disabled._
- Folder deletion. _Can delete folders that are not empty._
- Customizable directory path
- Rename files.
- Edit files. Ability to specify which files can be edited. _Can be configured to be enabled/disabled._
- Configurable idden files. Ability to specify files that should not be displayed. _Can be configured to be enabled/disabled._
- Simple User friendly interface - Clear error and success confirmation messages.
- User customizable interface - HTML and CSS is organized into HTML templates that can be customized without editing Php.

Setup
===============

- Copy files to a dedicated directory (eg. `\admin\`) on an Apache server with Php configured.
- `chmod 666` `config.ini` and the files in `\templates\`.
- `chmod 755 index.php`.
- Update configuration properties in `config.ini`.
- `chmod 777` the directory to be managed.
- Sample directory structure

```
 \admin
       \index.php
       \config.ini
       \css
           \styles.css
       \templates
                   \edit.html
                   \layout.html
                   \main.html
                   \main-row.html
                   \edit.html
 \content
         \... (directory to be managed)
```


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
- NEW: Externalized 905 of html into templates. Also improvement general formatting.
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
