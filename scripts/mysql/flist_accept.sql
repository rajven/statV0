SELECT f.id,User_auth.IP FROM User_auth, User_list, Group_filters G, filter_list f
WHERE f.id=G.Filtrid and G.Groupid=User_list.Group_id and User_list.id=User_auth.userid
and User_auth.enabled=1 and User_auth.nat=1 and User_auth.grpflt=1 and User_list.blocked=0 
and User_auth.deleted=0 and f.action=1
Order by f.id,User_auth.IP;

SELECT f.id,User_auth.IP FROM User_auth, User_list, User_filters UF, filter_list f
WHERE UF.Userid=User_auth.id and User_list.enabled=true and f.id=UF.Filterid
and User_list.blocked=0 and User_auth.deleted=0
and User_list.id=User_auth.userid and f.action=1
Order by f.id;