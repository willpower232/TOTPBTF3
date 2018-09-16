(function() {
	if (typeof window.refreshat !== 'undefined') {
		var timer = document.querySelector('.a-timer'),
			loop = setInterval(function() {
				var progress = Math.floor(((30 - (window.refreshat - (Date.now() / 1000))) / 30) * 100);

				if (progress >= 100) {
					timer.style.opacity = 0;

					// now is the time, stop ticking
					clearInterval(loop);

					// bump the refresh into the future to avoid double refresh
					setTimeout(function() {
						window.location.reload();
					}, 100);
				} else if (timer) {
					timer.style.setProperty('--progress', progress);
					timer.style.opacity = 1;
				}
			}, 1000);
	}

	if ((toggle = document.querySelector('input[name="light_mode"]'))) {
		toggle.removeAttribute('disabled');
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

	if ('clipboard' in navigator && (copybutton = document.querySelector('.js-copy'))) {
		copybutton.classList.add('enabled');

		copybutton.addEventListener('click', function(ev) {
			var text = document.querySelector(ev.target.dataset.copies).innerText;

			navigator.clipboard.writeText(text).then(function() {
				copybutton.classList.add('success');
			}).catch(function(err) {
				copybutton.classList.add('failure');
				console.log(err);
			}).finally(function() {
				setTimeout(function() {
					copybutton.classList.remove('success', 'failure');
				}, 800);
			});
		});
	}

	if ((breadcrumbs = document.querySelector('.breadcrumbs'))) {
		window.addEventListener('resize', function overflower() {
			var first = breadcrumbs.firstElementChild,
				last = breadcrumbs.lastElementChild,
				width = breadcrumbs.offsetWidth;

			if (last.offsetLeft + last.offsetWidth > width) {
				breadcrumbs.classList.add('overflowed');
			} else if (first.offsetLeft > 0) {
				breadcrumbs.classList.remove('overflowed');
			}

			// return function so we can complete the event listener
			// and run the function as we add it to the event
			return overflower;
		}());
	}

	if ('serviceWorker' in navigator) {
		navigator.serviceWorker.register('/service-worker.js');
	}
})();
