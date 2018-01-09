function resizeCentralPart()
{
	var centralPart = document.getElementById('centralPart');
	var banner = document.getElementById('topBanner');
	if(centralPart != null)
	{
		centralPart.style.height = window.innerHeight - 2*20  - banner.offsetTop - banner.clientHeight + "px";
		centralPart.style.width  = window.innerWidth - 2*20 + "px";
	}
}

window.addEventListener("load", function()
{
	//Set the central part correctly
	var banner = document.getElementById('topBanner');
	var centralPart = document.getElementById('centralPart');
	if(centralPart != null && banner != null)
			centralPart.style.marginTop = banner.offsetTop + banner.clientHeight + 20 + 'px';
	resizeCentralPart();
}, false);

window.addEventListener("resize", function()
{
	resizeCentralPart();
}, false);
