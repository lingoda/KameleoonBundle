# script is a slightly modified version of vendor/kameleoon/kameleoon-client-php/job/kameleoon-client-php-process-queries.sh

#kameleoon_work_dir=/app/linguando/var/kameleoon
kameleoon_work_dir="$1"

request_files=$("ls" -rt $kameleoon_work_dir/requests-*.sh 2>error.txt)
previous_minute=$(($("date" +"%s")/60-1))
for request_file in $request_files
do
	request_file_minute=$("echo" "$request_file" | "sed" "s/.*requests\-\(.*\)\.sh/\1/")
	if [ $request_file_minute -lt $previous_minute ]
	then
		"mv" -f $request_file "${request_file}.lock"
	fi
done
for request_file in $request_files
do
	if [ -f "${request_file}.lock" ]
	then
		"source" "${request_file}.lock";"rm" -f "${request_file}.lock"
	fi
done
