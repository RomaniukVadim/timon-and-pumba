<?php

$row_count = $db->querySingle('SELECT COUNT(*) FROM blog;');
$korova=mt_rand($maxrandkol, $row_count);

$randkol=mt_rand($minrandkol,$maxrandkol);
$results = $db->query('SELECT kluch,url FROM blog LIMIT '.$korova.','.$randkol);

echo '<ul>';
while ($row = $results->fetchArray()) {
	echo '<li>';
	echo '<a href="/'.$row["url"].'">'.$row["kluch"].'</a>';
	
	echo '</li>';

} //end while
echo '</ul>';
?>