function klappe(id)
{
	var klappText = document.getElementById('k' + id);
	var klappBild = document.getElementById('pic' + id); 

	if (klappText.style.display == 'none') {
  		klappText.style.display = 'block';
  		// klappBild.src = 'images/blank.gif';
	}
	else {
  		klappText.style.display = 'none';
  		// klappBild.src = 'images/blank.gif';
	}
}

function klappe_news(id)
{
	var klappText = document.getElementById('k' + id);
	var klappBild = document.getElementById('pic' + id); 

	if (klappText.style.display == 'none') {
  		klappText.style.display = 'block';
  		klappBild.src = 'pic/minus.png';
	}
	else {
  		klappText.style.display = 'none';
  		klappBild.src = 'pic/plus.png';
	}
}