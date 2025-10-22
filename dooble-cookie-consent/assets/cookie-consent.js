/* dooble cookies consent
 * By dooble
 * Version: 1.1
 * Last updated: 22/10/2025
 */
(function () {
	if( document.getElementById('od-accept') )
		document.getElementById('od-accept').focus();
	
	const COOKIE_NAME = 'od_consent';
	const COOKIE_DAYS = 365;

	const banner = document.getElementById('cookie-banner');
	const btnAccept = document.getElementById('od-accept');
	const btnDecline = document.getElementById('od-decline');

	function setCookie(name, value, days) {
		let expires = '';
		if (days) {
			const d = new Date();
			d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
			expires = '; expires=' + d.toUTCString();
		}
		document.cookie =
		name + '=' + encodeURIComponent(value) + expires +
		'; Path=/; Domain='+window.location.hostname+'; Secure; SameSite=Lax';
	}

	function getCookie(name) {
		const cookies = document.cookie ? document.cookie.split(';') : [];
		for (let i = 0; i < cookies.length; i++) {
			const c = cookies[i].trim();
			if (c.startsWith(name + '=')) {
				return decodeURIComponent(c.substring(name.length + 1));
			}
		}
		return null;
	}

	function showBannerIfNeeded() {
		const v = getCookie( COOKIE_NAME );
		if ( v == 1 ) {
			enableNonEssentialScripts();
		}
	}
	function loadScript(src) {
		const s = document.createElement('script');
		s.src = src;
		s.async = true;
		document.head.appendChild(s);
	}

	if( getCookie( COOKIE_NAME ) === null ) {
		document.querySelector('#cookie-banner').classList.add('active');
	}
	btnAccept?.addEventListener('click', function () {
		setCookie(COOKIE_NAME, 1, COOKIE_DAYS);
		banner.classList.add('od-hidden');
		enableNonEssentialScripts();
	});

	btnDecline?.addEventListener('click', function () {
		setCookie(COOKIE_NAME, 0, COOKIE_DAYS);
		banner.classList.add('od-hidden');
		// לא טוענים כלום
	});

	showBannerIfNeeded();
})();
