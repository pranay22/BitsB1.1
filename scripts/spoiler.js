var spoiler_collapse = false;
document.write('<style type="text/css">\n.spoiler-hidden{visibility:hidden}' + (spoiler_collapse ? '\ndiv.spoiler-hidden{display:none}' : '') + '\n<\/style>');
function spoilerToggle(o, reset)
{
	var c, t, v, i, j;
	reset = reset || false;
	if (o && (c = o.className.match(/\bspoiler-link|spoiler-box\b/)))
	{
		v = (c == 'spoiler-box');
		o = !v ? o.parentNode.nextSibling : o;
		c = o.firstChild;
		v = reset || !(v || c.className == 'spoiler-hidden');
		c.className = v ? 'spoiler-hidden' : 'spoiler-visible';

		if (o.previousSibling && (t = o.previousSibling.childNodes[0]))
		{
			t.innerHTML = v ? spoiler_show : spoiler_hide;
		}
		if (v && (c = c.childNodes))
		{
			for (i = 0; i < c.length; i++)
			{
				if (c[i].className && c[i].className.indexOf('spoiler') >= 0)
				{
					for(j = 0, t = c[i].childNodes; j < t.length; j++)
					{
						spoilerToggle(t[j], true);
					}
				}
			}
		}
	}
	return(false);
}