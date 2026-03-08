#!/bin/bash

line_count = $(wc -l $2)
lines_per_file = $(($line_count / $1))
mkdir "$3"
split -l $lines_per_file "$2" "$3"
