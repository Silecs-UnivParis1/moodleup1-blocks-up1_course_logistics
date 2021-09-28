function togglecollapseall(id) {
    var element = document.getElementById(id);
    if (element) {
		var enfants = element.childNodes;
		enfants.forEach(
			function(el, currentIndex, listObj) {
				if (el.tagName == 'I') {
					var classes = el.className;
					if (classes.indexOf("hidden") > 0) {
						el.classList.remove('hidden');
					} else {
						el.classList.add('hidden');
					}
				}
			});
			
		var liste = document.getElementById('bloc-'+id);
		if (liste) {
			var classe = liste.className;
			if (classe.indexOf("hidden") > 0) {
				liste.classList.remove('hidden');
			} else {
				liste.classList.add('hidden');
			}
		}
	}
}
