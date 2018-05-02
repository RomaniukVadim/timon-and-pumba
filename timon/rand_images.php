<?php

$row_count = $db->querySingle('SELECT COUNT(*) FROM blog;');
$korova=mt_rand($maxrandkol, $row_count);
$randkol=mt_rand($minrandkol,$maxrandkol);
$results = $db->query('SELECT kluch,url,pred FROM blog LIMIT '.$korova.','.$randkol);

echo '<br><br>Похожие посты:<br>';
while ($row = $results->fetchArray()) {
	$s=preg_match_all('~<img\ src=".*?>~',$row["pred"],$res);	
	echo '<div class="col-md-3">';
	echo '<a href="/'.$row["url"].'">'.$res[0][0].$row["kluch"].'</a>';
	echo '</div>';

} //end while

?>