<?php
require_once 'demetra.php';
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Demetra manual rating</title>
</head>
<body>
<form action="rate.php">
<label>url: </label>
<input type="text" name="url">
<br>
Voto: <label>sicuro<input type="radio" name="value" value="1"></label>
<label>potenzialmente difficile<input type="radio" name="value" value="2"></label>
<label>inadatto<input type="radio" name="value" value="3"></label>
<br>
<label>Solo sulla pagina<input type="radio" name="type" value="page" checked="checked"></label>
<label>All'intero dominio<input type="radio" name="type" value="domain"></label>
<br>

<label>Motivo: </label>
<select name="motivation">
<option value="">Non specificato</option>
<?php
$conn=demetra_connect();
$r=demetra_query($conn,'select id,name,label from motivation');
while( $row=mysqli_fetch_assoc($r) ) {
	echo '<option value="',$row['name'],'">',$row['label'],'</option>';
}

?>
</select>
<br>
<label>Nota: </label>
<textarea name="note"></textarea>
<input type="submit">
</form>
</body>
