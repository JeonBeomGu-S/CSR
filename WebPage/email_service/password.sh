#!/bin/bash
# email test script
# $1 : 인증번호
# $2 : 이메일
echo "CSR 계정 인증번호 : $1" | mail -a "From: CSR 시스템 엔지니어링 <someone@example.com>" -s "인증번호"  $2
