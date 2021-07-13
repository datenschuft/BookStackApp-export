# BookStackApp-export

If you do your it-documentation in bookstackapp-wiki (https://www.bookstackapp.com/) it could be nessesary to export some informations from the wiki and store the informations somewhere else. If you have to do a desaster-recovery maybe you would have some Informations from your IT-documentation offline avilable. With this Script you can Export yout BookstackWiki-pages.


This script will generate folders for each book, chapter, and export the page in pdf-format (on pdf-error in html -format) and add the number to the pages to represent the sorting of the pages.

Edit the config.php and change the required values.
then run

php runexport.php

It is designed to run on linux and writes to std-out.
So you could automate the export-run with cron.


If you would export attachments from pages to, this script has to run on the same host as bookstackapp-wiki, becaue it reads files from the webservers docroot upload directory
