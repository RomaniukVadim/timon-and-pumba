<?php

$ress=$db->query('SELECT COUNT(*) FROM blog;');
$row_count = $ress->fetch_row();

$korova=mt_rand($maxrandkol, $row_count[0]);

$randkol=mt_rand($minrandkol,$maxrandkol);
$results = $db->query('SELECT kluch,url FROM blog LIMIT '.$korova.','.$randkol);

echo '<ul>';
while($row = $results->fetch_assoc()) {
	echo '<li>';
	echo '<a href="/'.$row["url"].'">'.$row["kluch"].'</a>';
	
	echo '</li>';

} //end while
echo '</ul>';
?>