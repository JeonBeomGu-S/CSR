#!/bin/bash
# email test script
# $1 : 내용
# $2 : 제목
# $3 : 받는 사람
mail -a "From: CSR 시스템 엔지니어링 <csr@csr.com>" -s $2 $3  < $1
