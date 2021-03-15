SELECT IP FROM User_list,User_auth
where (User_list.id = User_auth.userid) and 
(((User_auth.enabled=0 or User_auth.proxy=0) and User_auth.deleted=0) or 
(User_list.blocked=1 and User_list.deleted=0 and User_list.enabled=1));
