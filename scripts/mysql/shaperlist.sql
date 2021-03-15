SELECT id,"0/0",IP,bandwidth FROM User_auth where User_auth.enabled=1 and User_auth.deleted=0 and User_auth.bandwidth>0 order by IP;

