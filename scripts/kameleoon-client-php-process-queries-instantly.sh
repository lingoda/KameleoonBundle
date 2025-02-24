#!/usr/bin/env bash
# this script takes all the tracking requests and sends them to Kameleoon instantly

kameleoon_work_dir="$1"
if [ -z "$kameleoon_work_dir" ]
then
    echo "Directory $kameleoon_work_dir does not exist."
    exit 1
fi


function remove_file {
    filter="${request_file:0:$((${#request_file} - 2))}*"
    rm -f $filter
}

request_files=$(ls -rt "$kameleoon_work_dir"/requests-*.sh 2>/dev/null)
selected_request_files=()

for request_file in $request_files; do
    mv -f "$request_file" "${request_file}.lock"
    selected_request_files+=("$request_file")
done

for request_file in "${selected_request_files[@]}"; do
    locked_request_file="${request_file}.lock"
    if [ -f "$locked_request_file" ]; then
        if [ -s "$locked_request_file" ]; then
            source "$locked_request_file"
            if [ $? -eq 0 ]; then
                remove_file
            else
                mv -f "$locked_request_file" "$request_file"
            fi
        else
            remove_file
        fi
    fi
done
