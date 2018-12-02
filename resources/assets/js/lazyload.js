(function(d) {
	var withIntersectionObserver = function(callback) {
		// apparently we can't test for false very well so base it off true instead
		if ('IntersectionObserver' in window) {
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
})(document);
