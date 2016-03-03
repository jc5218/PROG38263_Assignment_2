
# PROG38263_Assignment_2
Assignment 2 for the Sheridan College Secure Software Development class, by Joseph Chu and Rita Petros.

Version 1.0 of the assignment web application. Start from https://localhost/login.php.

Everything in the html folder should go into /var/www/html on Ubuntu, or whichever your working directory for your Apache PHP files is.

The default-ssl.conf file was pulled from /etc/apache2/sites-available/ for my Apache2 configuration and are the settings I used to get SSL/TLS to work in my Virtualbox Ubuntu setting. After changing the settings in that file, a 'sudo service apache2 reload' and 'sudo service apache2 restart' will be needed to update the new settings.

The users_tables.bmp, verify_table.bmp and user_image_table.bmp contain SQL descriptions of the users, verify, and user_image tables and their fields which this application uses.
