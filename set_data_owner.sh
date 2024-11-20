#!/bin/bash


web_server_user=$(ps aux | grep -E 'apache2|nginx' | grep -v 'grep' | awk '{print $1}' | sort | uniq | grep -v 'root')


if [ -z "$web_server_user" ]; then

    web_server_user="www-data"
fi


project_dir="$(dirname "$(realpath "$0")")"

file1="$project_dir/data/orders.json"
file2="$project_dir/data/products.json"
file3="$project_dir/data/logs.json"
file4="$project_dir/data/sse.json"

if [ -f "$file1" ] && [ -f "$file2" ] && [ -f "$file3" ] && [ -f "$file4" ]; then
    chown $web_server_user:$web_server_user $file1 $file2 $file3 $file4
fi