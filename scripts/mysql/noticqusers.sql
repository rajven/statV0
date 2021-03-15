SELECT IP FROM User_list,User_auth where User_list.id = User_auth.userid and User_auth.enabled=1 and User_auth.deleted=0 and User_list.blocked=0 and User_list.icq=0;

