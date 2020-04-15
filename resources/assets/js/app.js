(function(w, n, d) {
	var $ = function(selector, context) {
		return (context || d).querySelector(selector) || null;
	};

	// add the scrollup class if the page can scroll
	if (d.documentElement.scrollHeight > d.documentElement.clientHeight) {
		$('body').classList.add('scrollup');
	}

	// https://stackoverflow.com/a/45719399
	w.onscroll = function() {
		// "false" if direction is down and "true" if up
		var isup = (this.oldScroll > this.scrollY);

		if (isup) {
			$('body').classList.add('scrollup');
		} else {
			$('body').classList.remove('scrollup');
		}

		this.oldScroll = this.scrollY;
	};

	if (w.refreshat !== undefined) {
		var timer = $('.a-timer'),
            loop,
			progress;

		var updateProgress = function () {
			// must use these Math.floor's and `> 100` (not `>=`) to avoid double refresh
			// double refresh occurs because page loads before the final second of this code has fully passed
            progress = ((30 - (w.refreshat - (Date.now() / 1000))) / 30) * 100;
            if (progress > 100) {
                progress = 100;
            }
		};

		var shouldReload = function () {
			return (progress == 100);
		};

		var doReload = function () {
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
		};

        // tick the timer
        // - delay the reveal for one tick to avoid transition issues
        var shouldShow = null;
        setInterval(function () {
            updateProgress();
            if (timer) {
                timer.style.setProperty('--progress', progress);

                if (shouldReload()) {
                    timer.classList.remove('show');
                    timer.classList.add('done');
                } else if (shouldShow === null) {
                    shouldShow = true;
                } else if (shouldShow === true) {
                    timer.classList.add('show');
                }
            }
        }, 20);

		var startLoop = function () {
			loop = setInterval(function() {
				if (shouldReload()) {
					// now is the time, stop trying to reload
					stopLoop();

                    // guarantee the token has changed after the reload by delaying
					setTimeout(doReload, 1200);
				}
			}, 700);
		};

		var stopLoop = function () {
			clearInterval(loop);
		};

		startLoop();

		if (typeof d.addEventListener !== "undefined" && d.hidden !== undefined) {
			d.addEventListener('visibilitychange', function () {
				if (d.hidden) {
					// avoid page refresh whilst hidden
					stopLoop();
				} else {
					// page visible
					updateProgress();

					if (shouldReload()) {
						// need fresh token so reload
						doReload();
					} else {
						// continue as before
						startLoop();
					}
				}
			});
		}
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
