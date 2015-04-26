/******************************
bookmarking option for IE and FF
by d6bmg
******************************/
function bookmarksite(title, url){
if (document.all)
window.external.AddFavorite(url, title);
else if (window.sidebar)
window.sidebar.addPanel(title, url, "")
}