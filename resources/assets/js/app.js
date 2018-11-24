(function(w, n, d) {
	var $ = function(selector, context) {
		return (context || d).querySelector(selector) || null;
	};

	if (typeof w.refreshat !== 'undefined') {
		var timer = $('.a-timer'),
			loop = setInterval(function() {
				var progress = Math.floor(((30 - (w.refreshat - (Date.now() / 1000))) / 30) * 100);

				if (progress >= 100) {
					timer.style.opacity = 0;

					// now is the time, stop ticking
					clearInterval(loop);

					if (navigator.onLine) {
						// bump the refresh into the future to avoid double refresh
						setTimeout(function() {
							w.location.reload();
						}, 100);
					} else {
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

	if ((tgl = $('input[name="light_mode"]')) && 'fetch' in w) {
		tgl.removeAttribute('disabled');
		tgl.addEventListener('change', function(ev) {
			var data = new FormData();

			data.append('light_mode', tgl.checked);

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
				tgl.checked = !tgl.checked;
			}).catch(function(error) {
				// handle exception e.g. JSON syntax error
				alert('System error: ' + error);

				// revert toggle change for consistency
				tgl.checked = !tgl.checked;
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

	if ('clipboard' in n && (cpb = $('.js-copy'))) {
		cpb.classList.add('enabled');

		cpb.addEventListener('click', function(ev) {
			var text = $(ev.target.dataset.copies).innerText;

			n.clipboard.writeText(text).then(function() {
				cpb.classList.add('success');
			}).catch(function(err) {
				cpb.classList.add('failure');
				console.log(err);
			}).finally(function() {
				setTimeout(function() {
					cpb.classList.remove('success', 'failure');
				}, 800);
			});
		});
	}

	if ((bc = $('.breadcrumbs'))) {
		w.addEventListener('resize', function overflower() {
			var first = bc.firstElementChild,
				last = bc.lastElementChild,
				width = bc.offsetWidth;

			if (last.offsetLeft + last.offsetWidth > width) {
				bc.classList.add('overflowed');
			} else if (first.offsetLeft > 0) {
				bc.classList.remove('overflowed');
			}

			// return function so we can complete the event listener
			// and run the function as we add it to the event
			return overflower;
		}());
	}

	var withIntersectionObserver = function(callback) {
		// apparently we can't test for false very well so base it off true instead
		if ('IntersectionObserver' in w) {
			callback();
		} else {
			var teh = d.createElement('script');
			teh.src = '/js/intersectionobserver.min.js';
			teh.onload = function() {
				callback();
			};

			d.body.appendChild(teh);
		}
	};
	
	withIntersectionObserver(function() {
		var observer = new IntersectionObserver(function(entries) {
			entries.forEach(function(entry) {
				if (entry.intersectionRatio > 0) {
					observer.unobserve(entry.target);
					preloadImage(entry.target);
				}
			});
		}, {
			rootMargin: '50px 0px', // if the image gets within 50px in the X axis, start the download
			threshold: 0.01
		});

		Array.from(d.querySelectorAll('[data-src]')).forEach(function(image) {
			observer.observe(image);
		});
	});
	
	var preloadImage = function(el) {
		var img = new Image();

		img.onload = function() {
			el.src = el.dataset.src;
			delete el.dataset.src;
		};

		// not relevant but might be
		// img.onerror = function(err) {
		// 	el.parentNode.classList.add('error');
		// };
	
		img.src = el.dataset.src;
	};
})(window, navigator, document);
