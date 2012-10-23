function checkSubmit()
{
	var code1 = document.getElementById('code1');
	var code2 = document.getElementById('code2');
	var name = document.getElementById('country_name');
	var continent = document.getElementById('continent');
	var region = document.getElementById('region');
	var area = document.getElementById('area');
	var indepYear = document.getElementById('indepYear');
	var population = document.getElementById('population');
	var lifeExp = document.getElementById('lifeExp');
	var gnp = document.getElementById('gnp');
	var gnp_old = document.getElementById('gnp_old');
	var local_name = document.getElementById('local_name');
	var govt = document.getElementById('govt');
	var HoS = document.getElementById('HoS');
	var capital = document.getElementById('capital');
	var header = document.getElementById('lbl_header');
	var submitFlag = true;

	if (code1.value.length < 3)
	{
		submitFlag = false;
		document.getElementById('lbl_code1').className = "required_missing";
	}
	else
		document.getElementById('lbl_code1').className = 'required';

	if (code2.value.length < 2)
	{
		submitFlag = false;
		document.getElementById('lbl_code2').className = "required_missing";
	}
	else
		document.getElementById('lbl_code2').className = "required";

	if (name.value == '')
	{
		submitFlag = false;
		document.getElementById('lbl_name').className = "required_missing";
	}
	else
		document.getElementById('lbl_name').className = "required";

	if (region.value == '')
	{
		submitFlag = false;
		document.getElementById('lbl_region').className = "required_missing";
	}
	else
		document.getElementById('lbl_region').className = "required";


	if (area.value == '')
	{
		submitFlag = false;
		document.getElementById('lbl_area').className = "required_missing";
	}
	else
		document.getElementById('lbl_area').className = "required";

	if (population.value == '')
	{
		submitFlag = false;
		document.getElementById('lbl_pop').className = "required_missing";
	}
	else
		document.getElementById('lbl_pop').className = "required";

	if (local_name.value == '')
	{
		submitFlag = false;
		document.getElementById('lbl_local_name').className = "required_missing";
	}
	else
		document.getElementById('lbl_local_name').className = "required";


	if (govt.value == '')
	{
		submitFlag = false;
		document.getElementById('lbl_govt').className = "required_missing";
	}
	else
		document.getElementById('lbl_govt').className = "required_missing";

	if (!submitFlag)
	{
		header.innerHTML = '<b>Bold</b> fields are required. <span style=\'color:red\'>One or more fields is missing data.</span>';
	}

	return submitFlag;
}
