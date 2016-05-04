# PmaControl

_UI & CLI Tools for DBA (monitoring / backup / install / cleaner ...)_

This software is distribued under Free Software Lisense : GNU / GPL v3 (http://www.gnu.org/licenses/gpl-3.0.en.html)


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
* **ext-curl**
* **MySQL 5.6** / Perconna Server 5.6 / MariaDB 10.x => to store statistique / link of backup
* **graphviz** => make a graph about replication (include multi master and galera cluster)
* **apache2** (with a2enmod php5 & **a2enmod rewrite**)
* **postfix** to send mail
* **curl** used for get translatation from google

* **TokuDB** 

* Debian / Ubuntu
echo never > /sys/kernel/mm/transparent_hugepage/enabled
echo never > /sys/kernel/mm/transparent_hugepage/defrag

* RedHat 
echo never > /sys/kernel/mm/redhat_transparent_hugepage/enabled
echo never > /sys/kernel/mm/redhat_transparent_hugepage/defrag


in [mysqld] section

plugin-load=ha_tokudb

###Install composer

* `$ curl -sS https://getcomposer.org/installer | php`
* `$ mv composer.phar /usr/local/bin/composer`


### Deploy this project
* `git clone git@github.com:Glial/PmaControl.git pmacontrol`


###Install dependencies
* `cd pmacontrol`
* `git config core.fileMode false`
* `composer install`

### Auto install


* ./install

##You are ready !


* go to http://127.0.0.1/pmacontrol/

## Screenshots


![Alt text](/documentation/images/tree.png?raw=true "Replication tree")

![Alt text](/documentation/images/pluging cleaner.png?raw=true "Replication tree")

![Alt text](/documentation/images/query analizer.png?raw=true "Replication tree")

![Alt text](/documentation/images/storage area.png?raw=true "Replication tree")

![Alt text](/documentation/images/backup.png?raw=true "Replication tree")