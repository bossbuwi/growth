# growth - on indefinite hiatus
 _Growth is inevitable._

Setting up a working backend, be it for development or production, is difficult. As such, the steps written below needs to be followed in the correct order for the setup to be as painless and smooth as possible. A working knowledge of common software development terms like _text editor_ and _root folder_ will help immensely.

### Development Environment Preparation
**Note**   
This environment will use _phppgadmin_ running on XAMPP's Apache module for an easier viewing of the database's contents. The default _pgAdmin_ app meanwhile is used for the database setup. It can also be used to view the database's contents but _phppgadmin_ is much faster and easier to use.

1. Download and install [XAMPP](https://www.apachefriends.org/index.html). XAMPP version to be installed must have PHP version of at least 7.5. Note the directory where XAMPP is installed, it will be needed later.
    - On later versions of XAMMP (8+), `xampp-control.ini` on the xampp root directory has been _read-only_. This causes error when exiting XAMPP. To prevent the error, change the permissions of `xampp-control.ini` to _Full Access_.
2. Download and install [PostgreSQL](https://www.postgresql.org/download/).
    - Install PostgreSQL on `<xampp root folder>\pgsql\<postgresql version number>`. For example, in this document, XAMPP is installed on `C:\xampp`. Thus PostgreSQL's installation directory would be `C:\xampp\pgsql\13.3\`. Note the directory where PostgreSQL is installed, it will be needed later.
    - Install all PostgreSQL components.
    - Make sure that the data directory is at `<xampp root folder>\pgsql\<postgresql version number>\data`.
    - Enter a password for the root user. **It is crucial to note this password! Without it, the root account (postgres) cannot be used. If the password is in danger of being lost, use `admin` as the password instead.**
    - Leave the default port `(5432)` as is. If this port is in use on the machine, enter an unused port instead.
    - It is recommended to use just the default locale in order to prevent any inconsistencies.
    - There is no need to launch the Stack Builder after the installation is finished.
3. Open the `php.ini` file located at `<xampp root folder>\php\` using a text editor and uncomment the lines below.
    >extension=pdo_pgsql   
    >extension=pgsql
    - If there are no lines exactly like the above, uncomment the extension lines with the words `pdo_pgsql` and `pgsql`.
4. Open the `httpd.conf` file located at `<xampp root folder>\apache\conf` using a text editor and add the line below.
    >LoadFile "C:\xampp\php\libpq.dll"
    - Change `C:\xampp` to `<xampp root folder>` if XAMPP's root directory is not on the same directory as the example.
5. Create a directory named `phppgadmin` on `<xampp root folder>`. Download the `phpPgAdmin` codes as zip from the [official repository](https://github.com/phppgadmin/phppgadmin) and extract them on the newly created `phppgadmin`.
6. Go to `<xampp root folder>\phppgadmin\conf` and create a copy of the `config.inc.php-dist` file. Rename either the copy or the original to `config.inc.php`.
7. Open the `config.inc.php` file from _step 6_ using a text editor.
    - Find the `$conf['servers'][0]['host']` line and put `localhost` as it's value.
    - Find the `$conf['servers'][0]['pg_dump_path']` line and put `<postgres root folder>\\bin\\pg_dump.exe`. Make sure that all backslash ("\\") are double ("\\\\").
    - Find the `$conf['servers'][0]['pg_dumpall_path']` line and put `<postgres root folder>\\bin\\pg_dumpall.exe`. Make sure that all backslash ("\\") are double ("\\\\").
    - Find the `$conf['extra_login_security']` line and change its value to `false`. It may be left at the default value (`true`) but this will result in extra login steps that could slow down testing or investigation during software development.
    - The edited codes will be something like the ones below:
        >$conf['servers'][0]['host'] = 'localhost';   
        >$conf['servers'][0]['pg_dump_path'] = 'C:\\xampp\\pgsql\\13.3\\bin\\pg_dump.exe';   
	    >$conf['servers'][0]['pg_dumpall_path'] = 'C:\\xampp\\pgsql\\13.3\\bin\\pg_dumpall.exe';   
        >$conf['extra_login_security'] = false;
8. Open the `httpd-xampp.conf` file located at `<xampp root folder>\apache\conf\extra` using a text editor and add the codes below.
    >Alias /phppgadmin "C:/xampp/phppgadmin/"   
    ><directory "C:/xampp/phppgadmin">   
    >AllowOverride AuthConfig   
    >Require all granted   
    ></directory>   
    - Modify `C:/xampp` on the code depending on where XAMPP's root folder is.
    - Make sure that the codes are placed inside the outermost `<IfModule>`, i.e., the codes must be contained within the `<IfModule>` tag. Failure to do so will cause errors when XAMPP's Apache server is started. If there are any errors that prevents Apache from starting, always check on this step first.
9. Done! To check the PostgreSQL and the phppgadmin installation, launch XAMPP and start the Apache module. The installation is at `http://localhost/phppgadmin/`.
    - To log in, click the `PostgreSQL` under the `Servers` list on the left menu. Use the default `postgres` username and the password set on _step 2_.
    - If a white page with the error `Virtual Class -- cannot instantiate` is encountered on login, open `adodb.inc.php` file located on `<postgres root folder>\libraries\adodb` using a text editor. Find the line `die('Virtual Class -- cannot instantiate');` and delete it or comment it out. This is a bug resulting from using phppgadmin with PHP version greater than 8. Watch out on their [official repository](https://github.com/phppgadmin/phppgadmin) to see if any fixes have been rolled out.

### Setting up Laravel and the Growth Database

**Note**   
To proceed with the setup, the _Development Environment_ must have been created prior because it needs the PostgreSQL installation the PHP installation that came with XAMPP. A text editor or IDE is also needed to proceed. The recommended editor to use is [Visual Studio Code](https://code.visualstudio.com/) for its simplicity and ease of use.

1. Download and install [Composer](https://getcomposer.org/download/).
    - It is recommended to not tick the **_Developer Mode_** checkbox in order for the installer to create an entry in the machine's _Control Panel_.
    - When asked for the command line PHP to use, point the installer to `<xampp root folder>\php\php.exe`. If XAMPP is installed in `C:\xampp`, this part is automatically filled.
    - Do not enter a proxy unless one is needed to connect to the internet.
2. Open the `pgAdmin` app. It is usually listed on the _Start Menu_ but if not, it is located at `<postgres root folder>\pgAdmin (version)\bin\pgAdmin(version).exe`. It will ask for a master password that will be when using the pgAdmin app. **It is crucial to note this password! Without it, the pgAdmin app cannot be used. If the password is in danger of being lost, use `admin` as the password instead. If the password it lost, it is also possible to reset the master password and enter a new one.**
3. On the default Postgres installation, there is a server automatically created. Create a new database on it by right clicking on the server name, usually `PostgreSQL (version)` and selecting `Create` then `Database`.
    - Input the following details on the database creation screen.
        >Database: db_growth   
        >Owner: postgres   
        >Comment: Main database for growth app.   
    - Leave all other fields as blank or on their default values and click `Save` to finish the database creation.
4. Expand the newly created database on the pgAdmin app and look for `Schema`. Expand the Schema and make sure that a schema named `public` is existing. It is automatically created when a new database is made but if not, right click on the `Schema` group and select `Create` then `Schema`.
    - Input the following details on the schema creation screen.
        >Name: public   
        >Owner: postgres   
        >Comment: standard public schema
    - On the `Security` tab, add 2 new privileges with the information below.
        >**Privilege 1**   
        >Grantee: postgres   
        >Privileges: ALL   
        >**Privilege 2**   
        >Grantee: PUBLIC   
        >Privileges: ALL   
5. Clone the project from github into the machine.
6. Create a copy the `.env.example` file on the project's root folder. Rename the copy to `.env`. **Be very careful to not amend or delete the original `env.example` file because it is committed on the repository. The `.env` file is ignored so it could be edited freely depending on the environment setup that the machine has.
7. Open the project from using an IDE or a text editor. Go to the `.env` file and make sure that the lines below are setup correctly.
    >DB_CONNECTION=pgsql   
    >DB_HOST=127.0.0.1   
    >DB_PORT=5432   
    >DB_DATABASE=db_growth   
    >DB_SCHEMA=public   
    >DB_USERNAME=postgres   
    >DB_PASSWORD=`password set on step 2 of Developer Environment Preparation`   
8. Open a _command prompt window_ in the project's root folder and enter the following commands.
    >`composer install` (This will download all of the project's dependencies. It may take a while depending on the internet connection.)   
    >`php artisan key:generate` (This will generate a value on the `APP_KEY` line on the project's `.env` file from _step 6_.)   
    >`php artisan migrate` (This will setup the tables on the database.)   
9. Setup is done but the Laravel server could not be accessed yet. To be able to access the server, it needs to be setup.

### Setting up the server using XAMPP

**Note**   
For this setup, virtual hosts will be used. It is a bit difficult to setup but is easier to use on the long run. If difficulties are encountered during the setup, all of the steps may be bypassed by opening a _command prompt window_ on the project's root folder and entering `php artisan serve`. However, the frontend app that will connect to the server needs to adjust because the default _artisan server_ uses a different port than XAMPP.

1. Open the `httpd-vhost.conf` file located on `<xampp root folder>\apache\conf\extra` using a text editor and add the following lines.
    ><VirtualHost *:8080>   
    >DocumentRoot `"xampp root folder/htdocs/growth/public"`  
    ><Directory `"xampp root folder/htdocs/growth/public"`>   
    >Require all granted   
    >Options Indexes FollowSymLinks   
    >AllowOverride all   
    >Order Deny,Allow   
    >\</Directory>  
    >\</VirtualHost>
2. Run a _command prompt window_ as administrator and change directory to `xampp root folder\htdocs`.
3. Enter the command `mklink /D "xampp root folder\htdocs\growth" "project directory"`.
    - The above command will create a symbolic link on XAMPP's htdocs folder that points to the directory where the project was checked out.
    - The final command will look something like this.
        >mklink /D "C:\xampp\htdocs\growth"  "C:\github\growth"   
    - If done correctly, there should be a new folder named `growth` on XAMPP's htdocs folder that when opened will go to the project's sources.
4. Open the `httpd.conf` file located on `<xampp root folder>\apache\conf` using a text editor. Search for the line `Listen 80`. Add a line `Listen 8080` below it.
5. Launch XAMPP and start the Apache module. Laravel's default homepage can now be accessed on `http://localhost:8080/`.      
* There are sometimes errors when trying to access Laravel's default homepage. To solve these errors, open a _command prompt window_ on the project's root folder and try running the below commands in order.
    >php artisan view:clear   
    >php artisan config:clear   
    >php artisan cache:clear   
    >php artisan cache:clear   
    >php artisan optimize   
