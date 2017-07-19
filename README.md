# PHP - RESTful API and Client
Installation:
- copy files from 'service' folder into destination folder and run "composer install"
- create new table in DBMS and import rest.sql there
- configure DB settings in config/db.php file
- copy files from 'client' folder into destination folder and run "composer install"
- in index.php file provide your separate folder path (e.g. "define('URL', '/test-task')") and REST URL (e.g. "define('REST', 'https://site.com/api')") 