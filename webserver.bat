set mypath=%cd%
@echo %mypath%
cls
set /p port=Set port: 
cls
php -S 0.0.0.0:%port%