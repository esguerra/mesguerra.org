#!/bin/bash
if [ "$#" != "2" ]; then
    (
    echo "
Usage: `basename $0` <filename> <column_number>

     This bash script calls Rscript to produce a
     text-only histogram based on the stem function.
     You only have to provide a text-file and know the
     column where the desired data to be shown as a histogram
     is at.
"
    ) >&2
    exit 1
fi


#awk '{print $'''$2'''}' $1 | Rscript -e "fsizes <- as.numeric(readLines('/dev/stdin')); summary(fsizes); stem(fsizes, width=10, scale=2)"
#awk '{print $'''$2'''}' $1 | Rscript -e 'A <- (read.table("/dev/stdin", header=T)); summary(A)'

# For some reason the new R needs the pipe('cat   specification to work alrite.
#awk '{print $'''$2'''}' $1 | Rscript -e "A <- (read.table(pipe('cat /dev/stdin'))); summary(A)"
#awk '{print $'''$2'''}' $1 | Rscript -e "A <- (read.table(pipe('cat /dev/stdin'))); summary(A); stem(A[,1], width=10, scale=2)"
awk '{print $'''$2'''}' $1 | Rscript -e 'A <- read.table("stdin"); summary(A); stem(A[,1], width=10, scale=2)'
#awk '{print $'''$2'''}' $1 | Rscript -e 'A <- read.table("stdin"); summary(A); hist(A[,1])'
#awk '{print $'''$2'''}' $1 | Rscript -e "A <- (read.table(pipe('cat /dev/stdin'))); stem(A[,1])"
#seq 1 1000000 | time -p Rscript -e 'summary(read.csv("stdin", head=FALSE))'




#exit 0  # Will exit here only if command-line parameter present.

#grep "DYNA>" prod.out | awk '{print $4}' | Rscript -e "fsizes <- as.numeric(readLines('/dev/stdin')); summary(fsizes); stem(fsizes)"



