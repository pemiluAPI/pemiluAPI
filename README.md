# pemiluAPI

[![Build Status](https://travis-ci.org/pemiluAPI/pemiluAPI.png?branch=master)](https://travis-ci.org/pemiluAPI/pemiluAPI)

2014 Pemilu API

## Installation

The steps below assume you are working on a Ubuntu machine.

### Apache2 and PHP5

1. Install Apache2

    ```bash
	$ sudo apt-get install -y apache2
	```
1. Enable `mod_rewrite` and `mod_headers`

	```bash
	$ sudo a2enmod rewrite
	$ sudo a2enmod headers
	$ sudo service apache2 restart
	```
1. Install PHP5

    ```bash
    $ sudo apt-get install -y php5 libapache2-mod-php5 php5-cli php5-curl
    ```
    
    *For Ubuntu Saucy or above (>= 13.10), also install the following package:*
    
    ```bash
    $ sudo apt-get install php5-json
    ```
    
### Application

1. Install Git

	```bash
	$ sudo apt-get install -y git
	```

1. Clone this project

	```bash
	$ git clone https://github.com/pemiluAPI/pemiluAPI.git
	```

1. Go to the project directory and dowload Composer

    ```bash
    $ cd pemiluAPI
    $ wget http://getcomposer.org/composer.phar
    ```
1. Install the framework's dependencies

    ```bash
    $ php composer.phar install
    ```
1. Set the `web` directory as the `DocumentRoot`. Modify the `/etc/apache2/sites-available/default` file as follow

	```
	DocumentRoot /var/www/pemiluAPI/web
	<Directory /var/www/pemiluAPI/web>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride all
        Order allow,deny
        allow from all
    </Directory>
	```

	Please note that your modification need may vary depending on your Apache2 setup.

1. Restart Apache2

	```bash
	$ sudo service apache2 restart
	```

1. Open up http://127.0.0.1/status and you should see message

	```
	{"error":{"type":"invalid_request_error"}}
	```

1. Open up http://127.0.0.1 and you should see

	```
	{"error":{"message":"Unrecognized request URL (GET: \/).  Please see http:\/\/developer.pemiluapi.org\/.","type":"invalid_request_error"}}
	```
