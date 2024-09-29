@echo off
setlocal enabledelayedexpansion

::echo Script is starting...
set "result={"

:: Checking Apache web servers
for /f "tokens=*" %%i in ('httpd -v') do (
    set "apache_version=%%i"
    goto :check_nginx_version
)

:check_nginx_version
set "result=!result!"apache_version":"!apache_version!","

:: Checking Nginx web servers
for /f "tokens=*" %%i in ('nginx -v') do (
    set "nginx_version=%%i"
    goto :check_mysql_version
)

:check_mysql_version
set "result=!result!"nginx_version":"!nginx_version!","

:: Checking Mysql
for /f "tokens=*" %%i in ('mysql -v') do (
    set "mysql_version=%%i"
    goto :check_composer_version
)

:check_composer_version
set "result=!result!"mysql_version":"!mysql_version!","

:: Check Composer version
for /f "tokens=*" %%i in ('composer --version') do (
    set "composer_version=%%i"
    goto :check_php_version
)

:check_php_version
set "result=!result!"composer_version":"!composer_version!","

:: Check PHP version
for /f "tokens=*" %%i in ('php --version') do (
    set "php_version=%%i"
    goto :version_found
)

:version_found
::echo PHP version: !php_version!
set "result=!result!"php_version":"!php_version!","

:: Check PHP modules
set "modules_array="
for /f "skip=1 tokens=*" %%m in ('php -m') do (
    if not "%%m"=="" (
        set "modules_array=!modules_array!"%%m","
    )
)

:: Remove trailing comma if necessary
if defined modules_array (
    set "modules_array=!modules_array:~0,-1!"
)

:: Add modules to result
set "result=!result!"modules":[!modules_array!]"

:: Close the JSON object
set "result=!result!}"

:: Display the final JSON output without extra characters
echo !result!
endlocal