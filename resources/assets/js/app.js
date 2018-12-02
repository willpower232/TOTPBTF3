(function(w, n, d) {
	var $ = function(selector, context) {
		return (context || d).querySelector(selector) || null;
	};

	if (w.refreshat !== undefined) {
		var timer = $('.a-timer'),
			loop = setInterval(function() {
				// must use these Math.floor's and `> 100` (not `>=`) to avoid double refresh
				// double refresh occurs because page loads before the final second of this code has fully passed
				var progress = Math.floor(((30 - (w.refreshat - Math.floor(Date.now() / 1000))) / 30) * 100);
				if (progress > 100) {
					timer.style.opacity = 0;

					// now is the time, stop ticking
					clearInterval(loop);

					if (n.onLine) {
						w.location.reload();
					} else {
						var code;
						if ((code = $('.a-code'))) {
							code.innerText = 'offline please wait';
							code.style.letterSpacing = ".1em";
						}

						w.addEventListener('online', function() {
							w.location.reload();
						});
					}
				} else if (timer) {
					timer.style.setProperty('--progress', progress);
					timer.style.opacity = 1;
				}
			}, 1000);
	}

	var toggle;
	if ((toggle = $('input[name="light_mode"]')) && 'fetch' in w) {
		toggle.removeAttribute('disabled');
		toggle.addEventListener('change', function(ev) {
			var data = new FormData();

			data.append('light_mode', toggle.checked);

			fetch('/api/profile/setLightMode', {
				method: "POST",
				body: data,
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
					'Accept': 'application/json',
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').getAttribute('content'),
				},
				credentials: "same-origin"
			}).then(function(response) {
				if (response.ok) {
					return response.json();
				}

				// alert non 2xx HTTP
				alert('System error: received ' + response.status + ' ' + response.statusText);

				// revert toggle change for consistency
				toggle.checked = !toggle.checked;
			}).catch(function(error) {
				// handle exception e.g. JSON syntax error
				alert('System error: ' + error);

				// revert toggle change for consistency
				toggle.checked = !toggle.checked;
			}).then(function(output) {
				// if we've received JSON in HTTP 200 response, update the page
				if (output.current_state === true) {
					d.body.parentNode.classList.add('inverted');
				} else if (output.current_state === false) {
					d.body.parentNode.classList.remove('inverted');
				}
			});
		});
	}

	var clipboard;
	if ('clipboard' in n && (clipboard = $('.js-copy'))) {
		clipboard.classList.add('enabled');

		clipboard.addEventListener('click', function(ev) {
			var text = $(ev.target.dataset.copies).innerText;

			n.clipboard.writeText(text).then(function() {
				clipboard.classList.add('success');
			}).catch(function(err) {
				clipboard.classList.add('failure');
				console.log(err);
			}).finally(function() {
				setTimeout(function() {
					clipboard.classList.remove('success', 'failure');
				}, 800);
			});
		});
	}

	var breadcrumbs;
	if ((breadcrumbs = $('.breadcrumbs'))) {
		w.addEventListener('resize', function overflower() {
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
})(window, navigator, document);
