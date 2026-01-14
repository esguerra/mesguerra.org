Flat Calendar (w/FFDB)
Author: Josh Moore <josh@circulargenius.com>
Version: 1.1
http://www.circulargenius.com/flatcalendar/
--------------------------------------------------------------------------

CONTENTS
--------

1.0 Overview
2.0 Support Flat Calendar
3.0 Requirements
4.0 Installation
    4.1 How to install
    4.2 Security
    4.3 How to update
5.0 Using Flat Calendar
6.0 Bugs


1.0 Overview
------------

Flat Calendar provides the user a calendar in which events and event 
descriptions can be stored and accessed without requireing any form
of database access.

In 2002 I took on a job as a web developer. I was to develop a calandar in
which events could be stored and viewed. The obvious solution would be to use
some form of database and query language. However, these were not available to
me. After looking through millions of available scripts, it became apparent to 
me that there was a void to be filled. I couldn't find one script that did
what I wanted it to do, while looking fairly slick, that did not require
a database. So I wrote my own.

Flat Calendar uses a flat file database to store and retrieve events.
The only things required of the server are the ability to read and write
files and PHP4.0 support.

2.0 Support Flat Calendar
----------------------

If you use Flat Calendar, please show your support by leaving the product 
information for both Flat Calendar and FFDB in the footer of the HTML.
I am also interested to see your implementations of Flat Calendar! Email
me at josh@circulargenius.com and let me know how YOU are using Flat Calendar.

As always, developement takes time. And as we all learned in school, time is
money. If you feel it appropriate, any form of donation is much appreciated,
but not required, as this is a free product.


3.0 Requirements
----------------

The Flat Calendar package requires:

   o PHP4.0.0 or better
   o Ability to read/write (binary) files [1]


4.0 Installation
-----------------

4.1 How to Install:


- Unzip FlatCalendar.zip. (it should retain directory structure, creating a 'calendar'
  directory and within that an 'admin' directory)

- Upload the files to your web server. (directory structure must be retained on the web
  server)

- Set file permissions on both the 'calendar' and 'admin' directories so that scripts
  can read/write to files in those directories.

  From directory containing the 'calendar' directory:

chmod 777 calendar
chmod 777 calendar/admin


Everything should be in working order now. Loading the calendar.php file on your web
browser should create the database files.


4.2 Security:

Flat calendar is designed to work with .htaccess to allow only the correct people to 
view/update your calendar. .htaccess is password authentication provided by most 
webservers. A file is created called .htaccess which contains information about who
is allowed to access a directory.

For information on setting up an .htaccess file:
http://www.uoregon.edu/~cchome/htaccess/


If you would like everyone to be able to view your calendar,
but require password authentication for adding/editing/deleting events, place an 
appropriate .htaccess file in the 'admin' directory. If you would like only certain people
to be able to view the calendar as well, place an .htaccess file in the 'calendar' 
directory.


4.3 How to update
If updating from an old version, make SURE you do not delete the calendar.met or
calendar.dat files, as you will lose all your calendar information. Copy all files located
in this .zip file to your calendar and admin directories (appropriate files in appropriate
directories). This should update your calendar to the current version.

5.0 Using Flat Calendar
--------------

Using Flat Calendar should be fairly intuitive. 



6.0 Bugs & Updates
------------------

VERSION 1.1
-----------
-moved header and footer to their own files for easier configuration
-updated the FFDB to v2.7
-made the add and edit event functionality check for a blank event field.
Events titles must contain a letter, a-z/A-Z, or a number, 0-9. 

Right now Flat Calendar is in its beginning stages. It should work fine but lacks lots
of features I would like to add. Hopefully in the future I will be able to add more 
configuration (for things such as color schemes, etc.) as well as refining and cleaning
up some rather ugly code.

If you find a bug, or have ideas for ways in which Flat Calendar could be improved,
please contact me (josh@circulargenius.com).


