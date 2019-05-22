nubsantSocial = {};
nubsantSocial.likeCounter = 0;
nubsantSocial.followCounter = 0;
nubsantSocial.timesToRun = 100;
nubsantSocial.timeCounter = 1;
nubsantSocial.lang = 'en'; /*start*/
nubsantSocial.start = function(lang, qty) {
    if (lang != undefined) nubsantSocial.lang = lang;
    if(window.location.host == 'www.instagram.com') {
		if (!location.pathname.startsWith('/explore/tags/')) {
			nubsantSocial.getFollowers();
		} else {
			var posts = document.querySelectorAll('a[href*="/p"]').length;
			if (posts == 0) return;
			posts--;
			document.querySelectorAll('a[href*="/p"]')[Math.floor(Math.random() * posts)].click();
			if (qty != undefined) nubsantSocial.timesToRun = qty;
			window.setTimeout(function() {
				nubsantSocial.follow();
			}, 5000);
		}
	}
}; /*like and follow logic*/
nubsantSocial.next = function() {
    document.querySelector('a[class*="coreSpriteRightPaginationArrow"]').click();
    window.setTimeout(function() {
        nubsantSocial.follow();
    }, nubsantSocial.getRandomMillSeconds(5, 7));
};
nubsantSocial.follow = function() {
    var follow = Math.floor(Math.random() * 3);
    if (follow == 1) {
        var btn = document.querySelector('div[role="dialog"] button');
        if (btn.textContent == 'Seguir' || btn.textContent == 'Follow') {
            btn.click();
            nubsantSocial.followCounter++;
        }
    }
    window.setTimeout(function() {
        nubsantSocial.like();
    }, nubsantSocial.getRandomMillSeconds(2, 4));
};
nubsantSocial.like = function() {
    var like = Math.floor(Math.random() * 3);
    if (like == 1) {
        var btn = document.querySelector('span[aria-label="Me gusta"');
        if (btn == undefined) btn = document.querySelector('span[aria-label="Like"');
        btn.click();
        nubsantSocial.likeCounter++;
    }
    window.setTimeout(function() {
        console.log(nubsantSocial.timeCounter);
        if (nubsantSocial.timeCounter <= nubsantSocial.timesToRun) {
            nubsantSocial.timeCounter++;
            nubsantSocial.next();
        } else {
            var summary = '';
            if (nubsantSocial.lang == 'es') {
                summary += 'El proceso finalizó.';
                summary += '\nAleatoriamente se generó:';
                summary += '\n"Me Gusta":' + nubsantSocial.likeCounter + ' nuevos me gusta';
                summary += '\n"Siguiendo": ' + nubsantSocial.followCounter + ' nuevas cuentas';
            } else {
                summary += 'Process finished.';
                summary += '\nRandomly generated:';
                summary += '\n"Like":' + nubsantSocial.likeCounter + ' new likes';
                summary += '\n"Following": ' + nubsantSocial.followCounter + ' new accounts';
            }
            console.log(summary);
            alert(summary);
        }
    }, nubsantSocial.getRandomMillSeconds(4, 8));
};
nubsantSocial.getRandomMillSeconds = function(min, max) {
    return (Math.floor(Math.random() * (max - min)) + min) * 1000;
}; /*follower logic*/
nubsantSocial.followers = [];
nubsantSocial.following = [];
nubsantSocial.nonFollowers = [];
nubsantSocial.unFollowerBtns = [];
nubsantSocial.unFollowCounter = 0;
nubsantSocial.getFollowers = function() {
    document.querySelector('a[href*="/followers"]').click();
    window.setTimeout(function() {
        var container = document.querySelector('h1 div').parentNode.parentNode.parentNode.parentNode.children[1];
        scrollAsyncAll(container, 'followers');
    }, 5000);
};
nubsantSocial.getFollowings = function() {
    document.querySelector('a[href*="/following"]').click();
    window.setTimeout(function() {
        var container = document.querySelector('h1 div').parentNode.parentNode.parentNode.parentNode.children[2];
        scrollAsyncAll(container, 'followings');
    }, 5000);
};
nubsantSocial.removeNonFollowers = function() {
    nubsantSocial.following.forEach(function(item) {
        if (!nubsantSocial.followers.includes(item)) {
            nubsantSocial.nonFollowers.push(item);
        }
    });
    if (nubsantSocial.nonFollowers.length == 0) return;
    var container = document.querySelector('h1 div').parentNode.parentNode.parentNode.parentNode.children[2];
    container.querySelectorAll('div a').forEach(function(item) {
        if (nubsantSocial.nonFollowers.includes('/' + item.innerText + '/')) {
            nubsantSocial.unFollowerBtns.push(item.parentNode.parentNode.parentNode.parentNode.querySelector('div button'));
        }
    });
    if (nubsantSocial.unFollowerBtns.length > 0) nubsantSocial.unFollowClick(0);
};
nubsantSocial.unFollowClick = function(index) {	
    nubsantSocial.unFollowerBtns[index].click();
    window.setTimeout(function() {
        document.querySelectorAll('button[tabindex="0"]').forEach(function(item) {
            if (item.innerText == 'Dejar de seguir' || item.innerText == 'Unfollow') {
                item.click();
            }
        });
        index++;
		nubsantSocial.unFollowCounter++;
        if (nubsantSocial.unFollowerBtns[index] != undefined && nubsantSocial.unFollowCounter < 60) nubsantSocial.unFollowClick(index);
        else {
            var close = document.querySelector('button span[aria-label="Cerrar"]');
            if (close == undefined) document.querySelector('button span[aria-label="Close"]');
            close.click();
            var summary = '';
            if (nubsantSocial.lang == 'es') {
                summary += 'El proceso finalizó.';
                if (nubsantSocial.nonFollowers.length > 0) {
                    summary += '\nSe quitaron los siguiente usuarios que no te seguían:';
                    nubsantSocial.nonFollowers.forEach(function(item) {
                        summary += '\n' + item;
                    });
                }
            } else {
                summary += 'Process finished.';
                if (nubsantSocial.nonFollowers.length > 0) {
                    summary += '\nThe following users who did not follow you were removed:';
                    nubsantSocial.nonFollowers.forEach(function(item) {
                        summary += '\n' + item;
                    });
                }
            }
            console.log(summary);
            alert(summary);
        }
    }, nubsantSocial.getRandomMillSeconds(1, 3));
};

function scrollAsyncAll(container, type) {
    var currentHeight = container.firstChild.clientHeight - container.clientHeight;
    if (container.scrollTop >= currentHeight) { /* scrolled to the end */
        container.querySelectorAll('a').forEach(function(element) {
            if (type == 'followers') {
                if (!nubsantSocial.followers.includes(element.pathname)) nubsantSocial.followers.push(element.pathname);
            } else if (type == 'followings') {
                if (!nubsantSocial.following.includes(element.pathname)) nubsantSocial.following.push(element.pathname);
            }
        });
        console.log('Async scroll finished: ' + type);
        if (type == 'followers') {
            var close = document.querySelector('button span[aria-label="Cerrar"]');
            if (close == undefined) document.querySelector('button span[aria-label="Close"]');
            close.click();
            window.setTimeout(function() {
                nubsantSocial.getFollowings();
            }, 3000);
        } else if (type == 'followings') {
            window.setTimeout(function() {
                nubsantSocial.removeNonFollowers();
            }, 3000);
        }
        return;
    }
    container.scrollTop = currentHeight;
    window.setTimeout(function() {
        scrollAsyncAll(container, type);
    }, 5000);
};
