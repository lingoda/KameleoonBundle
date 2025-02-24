# script is a slightly modified version of vendor/kameleoon/kameleoon-client-php/job/kameleoon-client-php-process-queries.sh

#kameleoon_work_dir=/app/linguando/var/kameleoon

kameleoon_work_dir="$1"
if [ -z "$kameleoon_work_dir" ]
then
    echo "Directory $kameleoon_work_dir does not exist."
    exit 1
fi

function get_request_file_minute {
	request_file_minute=$("echo" "$request_file" | "sed" "s/.*requests\-\(.*\)-\(.*\)\-\(.*\)\.sh/\1/")
}

function remove_file {
	filter="${request_file:0:$((${#request_file} - 2))}*"
	"rm" -f $filter
}

# Processing all the request files
request_files=$("ls" -rt $kameleoon_work_dir/requests-*.sh 2>/dev/null)
selected_request_files=()
previous_minute=$(($("date" +"%s")/60-1))
for request_file in $request_files
do
	get_request_file_minute
	if [ $request_file_minute -lt $previous_minute ]
	then
		# The request file is older than 1 minute, so selecting it for tracking
		"mv" -f $request_file "${request_file}.lock"
		selected_request_files+=($request_file)
	fi
done

# Processing selected request files
expiration_time=$(($("date" +"%s")/60-120))
for request_file in ${selected_request_files[@]}
do
	locked_request_file="${request_file}.lock"
	if [ -f $locked_request_file ]
	then
		if [ -s $locked_request_file ]
		then
			"source" "$locked_request_file"
			if [ $? -eq 0 ]
			then
				# Tracking request succeeded, so removing the request file and body file
				remove_file
			else
				get_request_file_minute
				if [ $request_file_minute -lt $expiration_time ]
				then
					# The request file is older than 2 hours, so removing it and the body file
					remove_file
				else
					# Tracking request failed, so unlocking the request file
					"mv" -f $locked_request_file $request_file
				fi
			fi
		else
			# The request file is empty, so removing it and the body file
			remove_file
		fi
	fi
done
