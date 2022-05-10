customLoad = function() {
	console.log('custom Admin loaded (Extensions->Modules->Administrator->Custom Scripts && \administrator\templates\isis\css\custom.css)');
		
	if(document.querySelectorAll('.icon-user')[1] != undefined && document.querySelectorAll('.icon-user')[1].nextElementSibling != undefined) { 	
		if(document.querySelectorAll('.icon-user')[1].nextElementSibling.innerText != 'admin-en')
			localStorage.setItem('user','manager');
		else 
			localStorage.setItem('user','admin');
	}
	
	if(localStorage.getItem('user') == 'manager'){
		// hide menu options
		document.querySelector('#menu').children[0].querySelectorAll('li').forEach(function(item) {
			if(item.querySelector('.icon-home') == undefined)
				item.style.display = 'none';
			else
				item.firstChild.innerHTML = item.firstChild.innerHTML.replace('Main Menu', 'Menu Principal');
		});
		document.querySelector('#menu').children[1].querySelectorAll('li').forEach(function(item, index) {
			if(index == 1 || index == 2)
				item.style.display = 'none';    
		});		
	}
	else {
		// show filters
		if(document.querySelector('.js-stools-container-bar') != undefined)
		document.querySelector('.js-stools-container-bar').parentElement.parentElement.style.display = 'block';
	
		// show all tabs
		document.querySelectorAll('#myTabTabs li').forEach(function(item){		
			item.style.display = 'block';
		});
		// show right panels
		if(document.querySelector('.form-vertical') != undefined) {
			document.querySelectorAll('.form-vertical > *').forEach(function(item) {
				item.style.display = 'block';				
			});
			
		}
		// show all buttons
		document.querySelectorAll('#toolbar > div').forEach(function(item) {
			item.style.display = 'inline-block';
		});
	}

	// show side bar on media 
	if(location.search.indexOf('com_media') != -1) {
		document.getElementById('j-sidebar-container').style.display = 'block';
		document.getElementById('j-main-container').style.width = '82%';
	}	
	
	// set article menutype
	if(location.search == '?option=com_menus&view=item&client_id=0&menutype=mainmenu&layout=edit' ||
		location.search == '?option=com_menus&view=item&client_id=0&layout=edit') {
		if(document.getElementById('jform_request_id_name') == undefined) {
			window.parent.Joomla.submitbutton("item.setType", 'eyJpZCI6MCwidGl0bGUiOiJDT01fQ09OVEVOVF9BUlRJQ0xFX1ZJRVdfREVGQVVMVF9USVRMRSIsInJlcXVlc3QiOnsib3B0aW9uIjoiY29tX2NvbnRlbnQiLCJ2aWV3IjoiYXJ0aWNsZSJ9fQ==');
			window.parent.jQuery("#menuTypeModal").modal("hide");				
		}
	}
	if(location.search == '?option=com_menus&view=item&client_id=0&menutype=mainmenu&layout=edit' ||
		location.search == '?option=com_menus&view=item&client_id=0&layout=edit') {
		// show menu parent
		document.querySelector('.form-vertical').children[2].style.display = 'block';
	}
};