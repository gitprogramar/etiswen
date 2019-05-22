// Copyright 2018 The Chromium Authors. All rights reserved.
// Use of this source code is governed by a BSD-style license that can be
// found in the LICENSE file.

'use strict';

window.onload = function() {
	var chromeLang = chrome.i18n.getUILanguage().indexOf('es-') == 0 ? 'es' : 'en';
	document.getElementById('title').innerText = chromeLang == 'es' ? 'Nubsant Robot de redes sociales' : 'Nubsant Social Networks Robot';
	document.getElementById('startBtn').innerText = chromeLang == 'es' ? 'INICIAR' : 'START';
	document.getElementById('info').innerText = chromeLang == 'es' ? 'Robot que da "me gusta" y aumenta seguidores automaticamente' : 'Robot that do "likes" and increases followers automatically';
	document.getElementById('quantityLbl').innerText = chromeLang == 'es' ? 'Cantidad:' : 'Quantity:';
};

startBtn.onclick = function(element) { 	
	var chromeLang = chrome.i18n.getUILanguage().indexOf('es-') == 0 ? 'es' : 'en';
	var quantity = document.getElementById('quantity').value;
    chrome.tabs.query({active: true, currentWindow: true}, function(tabs) {
      chrome.tabs.executeScript(
          tabs[0].id,
          {code: 'nubsantSocial.start(\''+ chromeLang +'\', \'' + (parseInt(quantity) != 'NaN' ? quantity : 100) + '\');'});
    });
  };
