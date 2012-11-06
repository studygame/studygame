<?php

include 'connect.php';
if (isset($_POST['submit']))							//check that submit button was pressed
{
	$code1 = $_POST['code1'];						//get POST variables or set defaults
	$code2 = $_POST['code2'];
	$name = $_POST['country_name'];
	$continent = $_POST['continent'];
	$region = $_POST['region'];
	$indep = $_POST['indepYear'];
	if($indep == null)
	{
		$indep = 0;
	}
	$population = $_POST['population'];
	$lifeExp = $_POST['lifeExp'];
	if($lifeExp == null)
	{
		$lifeExp = 0;
	}
	$gnp = $_POST['gnp'];
	if($gnp == null)
	{
		$gnp = 0;
	}
	$gnp_old = $_POST['gnp_old'];
	if($gnp_old == null)
	{
		$gnp_old = 0;
	}
	$local_name = $_POST['local_name'];
	$gov = $_POST['govt'];
	$HoS = $_POST['HoS'];
	if($HoS == null)
	{
		$HoS = ' ';
	}
	$capital = $_POST['capital'];
	if($capital == null)
	{
		$capital = 0;
	}
	$area = $_POST['area'];
	if($area== null)
	{
		$area = 0;
	}
	
	$query = "INSERT INTO country (countrycode, name, continent, region, surfacearea, indepyear, population, lifeexpectancy, gnp, gnpold, localname, governmentform, headofstate, capital, code2) VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14,$15);";
	$stmt = pg_prepare($connection, "insert", $query);				//insert query for new country
	
	$result = pg_execute($connection, "insert", array($code1, $name, $continent, $region, $area, $indep, $population, $lifeExp, $gnp, $gnp_old, $local_name, $gov, $HoS, $capital, $code2));
	if($result){
		echo "Insertion Successful! Fill out required fields again to insert another country to the table, or follow the link to return to the home page!</br></br>";
	}
	else{
		echo 'Query was unsuccessful. The primary key of the country table is the country code. </br>';
		echo 'It may be that the country code you entered already exists in the table. Please use a different one.</br>';
	}
}
?>

<html>
<head>
<title>Lab 5</title>
<link rel='stylesheet' href='InsertForm.css' type='text/css' media='all' />
<script src='InsertForm.js'></script>
</head>
<body>
<div id='insert_form'>
<form method='POST' action='insert.php' onsubmit='return checkSubmit()'>
  <label id='lbl_header'><b>Bold</b> fields are required.</label>
  <br /><br />
  <label class='required' for='code1' id='lbl_code1'>* Country code</label>
  <input type='text' name='code1' id='code1' maxlength='3' size='3'/>
  <br /><br />
  <label class='required' for='code2' id='lbl_code2'>* Country code (abbr.)</label>
  <input type='text' name='code2' id='code2' maxlength='2' size='2'/>
  <br /><br />
  <label class='required' for='country_name' id='lbl_name'>* Country name</label>
  <input type='text' name='country_name' id='country_name' maxlength='52'/>
  <br /><br />
  <label class='required' for='continent' id='lbl_continent'>* Continent</label>
  <select name='continent' id='continent'>
    <option value='Africa'>Africa</option>
    <option value='Antarctica'>Antarctica</option>
    <option value='Asia'>Asia</option>
    <option value='Europe'>Europe</option>
    <option value='North America'>North America</option>
    <option value='Oceania'>Oceania</option>
    <option value='South America'>South America</option>
  </select>
  <br /><br />
  <label class='required' for='region' id='lbl_region'>* Region</label>
  <input type='text' name='region' id='region' maxlength='26' />
  <br /><br />
  <label class='required' for='area' id='lbl_area'>* Surface area</label>
  <input type='text' name='area' id='area' />
  <br /><br />
  <label for='indepYear' id='lbl_year'>Year of independence</label>
  <input type='text' name='indepYear' id='indepYear' />
  <br /><br />
  <label class='required' for='population' id='lbl_pop'>* Population</label>
  <input type='text' name='population' id='population' />
  <br /><br />
  <label for='lifeExp' id='lbl_lifeExp'>Life expectancy</label>
  <input type='text' name='lifeExp' id='lifeExp' />
  <br /><br />
  <label for='gnp' id='lbl_gnp'>GNP</label>
  <input type='text' name='gnp' id='gnp' />
  <br /><br />
  <label for='gnp_old' id='lbl_gnp_old'>GNP (old)</label>
  <input type='text' name='gnp_old' id='gnp_old' />
  <br /><br />
  <label class='required' for='local_name' id='lbl_local_name'>* Local name</label>
  <input type='text' name='local_name' id='local_name' maxlength='45' />
  <br /><br />
  <label class='required' for='govt' id='lbl_govt'>* Form of government</label>
  <input type='text' name='govt' id='govt' maxlength='45' />
  <br /><br />
  <label for='HoS' id='lbl_HoS'>Head of state</label>
  <input type='text' name='HoS' id='HoS' maxlength='60' />
  <br /><br />
  <label for='capital' id='lbl_capital'>Capital code</label>
  <input type='text' name='capital' id='capital' />
  <br /><br />
  <label><a href='index.php'>Return to Home Page</a></label>
  <input type='submit' name='submit' value='Insert row' />
</form>
</div>
</body>
</html>

