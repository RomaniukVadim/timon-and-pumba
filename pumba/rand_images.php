<?php

$ress=$db->query('SELECT COUNT(*) FROM blog;');
$row_count = $ress->fetch_row();

$korova=mt_rand($maxrandkol, $row_count[0]);

$randkol=mt_rand($minrandkol,$maxrandkol);

$results = $db->query('SELECT kluch,url,pred FROM blog LIMIT '.$korova.','.$randkol);


while($row = $results->fetch_assoc()) {
	$s=preg_match_all('~<img\ src=".*?>~',$row["pred"],$res);	
	echo '<div class="col-md-3">';
	echo '<a href="/'.$row["url"].'">'.$row["kluch"].$res[0][0].'</a>';
	echo '</div>';

} //end while
	
?>