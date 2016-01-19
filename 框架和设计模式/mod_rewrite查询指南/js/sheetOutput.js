var sheetOutput = document.getElementsByClassName('m-sheet-output')[0],
	sheetLists = sheetOutput.getElementsByClassName('sheet-output');

sheetOutput.addEventListener('click', function(event){
	var target = event.target;
	if (target.nodeName.toLowerCase() === 'h3') {
		var list = target.nextElementSibling;
		if (list) {
			if (list.style.display === 'none') {
				list.style.display = 'block';
			} else {
				list.style.display = 'none';
			};
			
		};
	};
}, false);
