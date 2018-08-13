(function() {
	if (typeof window.refreshat !== 'undefined') {
		var loop = setInterval(function() {
			if (window.refreshat < (Date.now() / 1000)) {
				// now is the time, stop ticking
				clearInterval(loop);

				// bump the refresh into the future to avoid double refresh
				setTimeout(function() {
					window.location.reload();
				}, 500);
			}
		}, 1000);
	}

	if ((toggle = document.querySelector('input[name="light_mode"]'))) {
		toggle.addEventListener('change', function(ev) {
			var xhr = new XMLHttpRequest(),
				data = new FormData();

			data.append('light_mode', toggle.checked);

			xhr.open('POST', '/api/profile/setLightMode');
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			xhr.setRequestHeader('Accept', 'application/json');

			xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

			xhr.onload = function() {
				var output = this.responseText;

				if (this.status == 200) {
					try {
						output = JSON.parse(output);

						if (output.current_state === true) {
							document.body.parentNode.classList.add('inverted');
						} else if (output.current_state === false) {
							document.body.parentNode.classList.remove('inverted');
						}
					} catch (ex) {
						alert('Not received JSON');
					}
				} else {
					alert('Received: ' + this.status);
				}
			};

			xhr.send(data);
		});
	}

	if ('serviceWorker' in navigator) {
		navigator.serviceWorker.register('/service-worker.js');
	}
})();
