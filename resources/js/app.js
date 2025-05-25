import.meta.glob([
    '../img/fab/**',
]);

class CodeAndTimer extends HTMLElement {
    #code;
    #timer;

    #refreshat;
    #timeout;

    connectedCallback() {
        this.#code = this.querySelector('.a-code');
        this.#timer = this.querySelector('.a-timer');

        this.#refreshat = this.getAttribute('refresh-at');

        // start the process
        this.setCode();

        // tick the timer
        // - this effectively animates the timer
        //   without css transitions
        setInterval(() => {
            // subtract from 30 to countdown from the right side
            let progress = ((30 - this.timeLeft()) / 30) * 100;

            if (progress > 100) {
                progress = 100;
            }

            this.#timer.style.setProperty('--progress', progress);
        }, 20);

        if (typeof document.addEventListener !== "undefined" && document.hidden !== undefined) {
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    // avoid page refresh whilst hidden
                    clearTimeout(this.#timeout);
                } else {
                    this.setCode();
                }
            });
        }
    }

    doReload() {
        // hilarious reach around to deal with this changing contexts
        const el = document.querySelector('code-and-timer');

        if (navigator.onLine) {
            removeEventListener('online', el.doReload);

            fetch(location, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
                .then((response) => response.json())
                .then((json) => {
                    el.setCode(json.code, json.refreshat);
                });
        } else {
            el.#code.innerText = 'offline';

            addEventListener('online', el.doReload);
        }
    }

    timeLeft() {
        // we're expecting this to be up to 30 seconds in the future so counting down
        return this.#refreshat - (Date.now() / 1000);
    }

    setCode(code, refreshat) {
        // allow us to avoid passing arguments to start the system
        if (code && refreshat) {
            this.#code.innerText = code;
            this.#refreshat = refreshat;
        }

        // whenever we set the code, set a time to replace that code
        this.#timeout = setTimeout(this.doReload, (this.timeLeft() + 1) * 1000);

        // make sure we can see the thing
        this.#timer.classList.add('show');
    }
};

customElements.define('code-and-timer', CodeAndTimer);

window.onload = function () {
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
};
