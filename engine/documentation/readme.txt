WWCMSv2 by AXE @ www.web-wow.net all rights reserved.

DATABASES:
----------
Website database MUST be on same SQL host as ACCOUNT database.
Character databases can be on different SQL hosts.


Example: Connecting to realm DB (realm 0 - this means its 1st realm)
$db2=connect_realm(0);
$a=$db2->query("SELECT * FROM characters where name ='Axe' LIMIT 1") or die($db2->error('error_msg'));
$b=$db2->getRow($a);
print_r($b);



SENDMAIL REMOTE ACCESS (RA):
----------------------------
Error Msg's:
User you tryed to connect to console does not have enough privilages to do it.
 - for trinity: go to accounts database, account access table check your acc
   id and make sure its on realmID = -1, gmlevel must be high enough.

USER is already logged in to remote console please try again later.
 - you need to wait unitl user is logged out from telnet remote console
   We suggest using unique account just for website, becouse if your using
   RA with your client on same acc, it will block website for that period of time.

Trinity server is offline, you must do this when server is online.
 - rare, but if server suddendly crashed or something, it will display this.

Port 3443 on localhost is closed.
 - Indicates wrong port, wrong host, port fowarding doesn't work
   or Trinity/MaNGOS server is offline.