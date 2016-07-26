@echo off
set path=D:\wamp\bin\mysql\mysql5.5.24\bin;
set yy=%date:~0,4%
set mm=%date:~5,2%
set dd=%date:~8,2%
if "%time:~0,2%" lss "10" (set hh=0%time:~1,1%) else (set hh=%time:~0,2%)
set ii=%time:~3,2%
set ss=%time:~6,2%
mysqldump -u root --password=123456 --database app > .\..\bak\%yy%%mm%%dd%%hh%%ii%%ss%.sql
