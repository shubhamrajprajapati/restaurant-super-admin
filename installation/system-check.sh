#!/bin/sh

# Initialize result as a JSON object
result="{"

# Function to append key-value pairs to result
append_to_result() {
    key="$1"
    value="$2"
    result="$result\"$key\":\"$value\","
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

# Check Composer version
composer_version=$(composer --version 2>&1 | grep -oP 'Composer \K\d+\.\d+\.\d+')
if [ $? -eq 0 ]; then
    append_to_result "composer_version" "$composer_version"
fi

# Check PHP version
php_version=$(php -v 2>&1 | grep -oP 'PHP \K\d+\.\d+\.\d+')
if [ $? -eq 0 ]; then
    append_to_result "php_version" "$php_version"
fi

# Check Git version
git_version=$(git --version 2>&1 | grep -oP '\d+\.\d+\.\d+')
if [ $? -eq 0 ]; then
    append_to_result "git_version" "$git_version"
fi

# Check PHP modules
modules_array=""
php_modules=$(php -m | tail -n +2) # Skip the first line
for line in $php_modules; do
    if [ -n "$line" ]; then
        modules_array="$modules_array\"$line\","
    fi
done

# Remove trailing comma from modules_array if necessary
if [ -n "$modules_array" ]; then
    modules_array="${modules_array%,}" # Remove the last comma
fi

# Add modules to result if any
if [ -n "$modules_array" ]; then
    result="$result\"modules\":[${modules_array}],"
fi

# Remove trailing comma if necessary
if [ -n "$result" ] && [ "${result##*,}" = "," ]; then
    result="${result%,}" # Remove the last comma
fi

# Close the JSON object
result="$result}"

# Display the final JSON output
echo "$result"