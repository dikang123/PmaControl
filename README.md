# PmaControl

_UI & CLI Tools for DBA (monitoring / backup / install / cleaner ...)_

**Distribution based on Linux. (don't work with windows!)**


* Monitoring : Master/Slave, Galera Cluster, Graph
* Query analyzer
* Backup system (Xtrabackup / mysqldump / mydumper) with different storage area
* Manage array of servers (like it's was only one)
* Manage user
* Pluging : Cleaner

##Deployment

###Install server

have a look on : https://github.com/Esysteme/Debian/blob/master/ubuntu_server.bash


###Dependencies to install

* **PHP 5.5.*** or highter
* **ext-gd**
* **ext-mcrypt**
* **ext-ssh2** => used for monitoring system and backup
* **ext-mysqlnd**
* **MySQL 5.6** / Perconna Server 5.6 / MariaDB 10.x => to store statistique / link of backup
* **graphviz** => make a graph about replication (include multi master and galera cluster)
* **apache2** (with a2enmod php5 & **a2enmod rewrite**)
* **postfix** to send mail

###Install composer

* `$ curl -sS https://getcomposer.org/installer | php`
* `$ mv composer.phar /usr/local/bin/composer`



### Deploy this project
* `git clone git@github.com:Glial/PmaControl.git pmacontrol`


###Install dependencies
* `cd pmacontrol`
* `git config core.fileMode false`
* `composer install`


###Install Database

* `mysql> CREATE DATABASE pmacontrol;`
* `mysql -h localhost -u root -p pmacontrol < sql/pmacontrol.sql`

###Edit config files

* `vi configuration/db.config.ini.php`

  * [name_of_connection] => will be acceded in framework with $this->di['db']->sql('name_of_connection')->method(), please use hostname, the '-' hyphen are not allowed there.
  * driver => list of SGBD avaible {mysql, postgresql, sybase, oracle}, Only MySQL's servers are monitored yet
  * hostname => server_name of ip of server SGBD (better to put localhost or real IP)
  * user => user who will be used to connect to the SGBD
  * password => password who will be used to connect to the SGBD
  * database => database / schema witch will be used to access to datas
  * ssh_login => used for backup tools, monitoring system 
  * ssh_password => passwd of ssh account
  * is_sudo => if the user is sudo (if not considered as root user)
  * tag ="production,france sartrouville" => tags must be seaprated by coma or space 
  * The database have to exist, if not create it
  

* `vi configuration/db.config.php`

  * DB_DEFAULT have to be the same used by [name_of_connection], if you set [pmacontrol]
  * you should have : ```define("DB_DEFAULT", "pmacontrol");```
  * it's made to determine what is the main connection to store data for pmacontrol


* `vi configuration/webroot.config.php`

 * if you use a direrct DNS set : define('WWW_ROOT', "/");
 * if you dev in local or other use : define('WWW_ROOT', "/path_to_the_final_directory/");
 * example : http://127.0.0.1/directory/myapplication/ => define('WWW_ROOT', "/directory/myapplication/");
 * Don't forget the final "/"


### add right to write

* `chown -R www-data:www-data tmp/`

###Generate cash (table & rights)

* `cd application/webroot`
* `php index.php administration admin_table`
* `php index.php administration generate_model`

## Start to collect data

* `php index.php pma_cli daemon`





##You are ready !


* go to http://127.0.0.1/pmacontrol/

## Screenshot


![Alt text](/documentation/images/tree.png?raw=true "Replication tree")

![Alt text](/documentation/images/pluging cleaner.png?raw=true "Replication tree")

![Alt text](/documentation/images/query analizer.png?raw=true "Replication tree")

![Alt text](/documentation/images/storage area.png?raw=true "Replication tree")

![Alt text](/documentation/images/backup.png?raw=true "Replication tree")

