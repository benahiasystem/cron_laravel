crond -f

supervisorctl reread
 
supervisorctl update
 
supervisorctl start "supervisor:*"