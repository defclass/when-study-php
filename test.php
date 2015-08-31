<?php
$link=mysql_pconnect("127.0.0.1","root","",true);
mysql_select_db("market",$link);
$sql=mysql_query("SELECT * FROM zf_admin");
while($rt=mysql_fetch_array($sql)){
    echo $rt['user']."\n";
}

?>
