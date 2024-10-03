#!/bin/sh

# Initialize result as a JSON object
result="{"

# Function to append key-value pairs to result
append_to_result() {
    key="$1"
    value="$2"
    # Check if result already has content to avoid leading commas
    if [ "${result}" != "{" ]; then
        result="$result,"
    fi
    result="$result\"$key\":\"$value\""
}

# Check Apache version
apache_version=$(httpd -v 2>&1 | grep -oP 'Apache/\K[^ ]+')
if [ $? -eq 0 ]; then
    append_to_result "apache_version" "$apache_version"
fi

# Check Nginx version
nginx_version=$(nginx -v 2>&1 | grep -oP 'nginx/\K[^ ]+')
if [ $? -eq 0 ]; then
    append_to_result "nginx_version" "$nginx_version"
fi

# Check MySQL version
mysql_version=$(mysql -V 2>&1 | grep -oP '\d+\.\d+\.\d+')
if [ $? -eq 0 ]; then
    append_to_result "mysql_version" "$mysql_version"
fi

# Check if running as root using id -u
if [ "$(id -u)" -eq 0 ]; then
    composer_output=$(COMPOSER_ALLOW_SUPERUSER=1 composer --version --no-interaction 2>&1)
else
    composer_output=$(composer --version --no-interaction 2>&1)
fi

# Generalized regex to capture the version number
composer_version=$(echo "$composer_output" | grep -oP 'Composer (version )?\K[0-9]+\.[0-9]+\.[0-9]+')
if [ $? -eq 0 ]; then
    append_to_result "composer_version" "$composer_version"
else
    append_to_result "composer_version" "not found"
fi

# Check Git version
git_version=$(git --version 2>&1 | grep -oP '\d+\.\d+\.\d+')
if [ $? -eq 0 ]; then
    append_to_result "git_version" "$git_version"
fi

# Check PHP version
php_version=$(php -v 2>&1 | grep -oP 'PHP \K\d+\.\d+\.\d+')
if [ $? -eq 0 ]; then
    append_to_result "php_version" "$php_version"
fi

# Check PHP modules
modules_array=""
php_modules=$(php -m | tail -n +2) # Skip the first line
for line in $php_modules; do
    if [ -n "$line" ]; then
        if [ -n "$modules_array" ]; then
            modules_array="$modules_array,"
        fi
        modules_array="$modules_array\"$line\""
    fi
done

# Add modules to result if any
if [ -n "$modules_array" ]; then
    result="$result,\"modules\":[${modules_array}]"
fi

# Close the JSON object
result="$result}"

# Display the final JSON output
echo "$result"