!function(e,t,n){var a,r,i,o=function(e,t){return(t||n).querySelector(e)||null};if(void 0!==e.refreshat)var s=o(".a-timer"),c=setInterval(function(){var n,a=Math.floor((30-(e.refreshat-Math.floor(Date.now()/1e3)))/30*100);a>100?(s.style.opacity=0,clearInterval(c),t.onLine?e.location.reload():((n=o(".a-code"))&&(n.innerText="offline please wait",n.style.letterSpacing=".1em"),e.addEventListener("online",function(){e.location.reload()}))):s&&(s.style.setProperty("--progress",a),s.style.opacity=1)},1e3);(a=o('input[name="light_mode"]'))&&"fetch"in e&&(a.removeAttribute("disabled"),a.addEventListener("change",function(e){var t=new FormData;t.append("light_mode",a.checked),fetch("/api/profile/setLightMode",{method:"POST",body:t,headers:{"X-Requested-With":"XMLHttpRequest",Accept:"application/json","X-CSRF-TOKEN":o('meta[name="csrf-token"]').getAttribute("content")},credentials:"same-origin"}).then(function(e){if(e.ok)return e.json();alert("System error: received "+e.status+" "+e.statusText),a.checked=!a.checked}).catch(function(e){alert("System error: "+e),a.checked=!a.checked}).then(function(e){!0===e.current_state?n.body.parentNode.classList.add("inverted"):!1===e.current_state&&n.body.parentNode.classList.remove("inverted")})})),"clipboard"in t&&(r=o(".js-copy"))&&(r.classList.add("enabled"),r.addEventListener("click",function(e){var n=o(e.target.dataset.copies).innerText;t.clipboard.writeText(n).then(function(){r.classList.add("success")}).catch(function(e){r.classList.add("failure"),console.log(e)}).finally(function(){setTimeout(function(){r.classList.remove("success","failure")},800)})})),(i=o(".breadcrumbs"))&&e.addEventListener("resize",function e(){var t=i.firstElementChild,n=i.lastElementChild,a=i.offsetWidth;return n.offsetLeft+n.offsetWidth>a?i.classList.add("overflowed"):t.offsetLeft>0&&i.classList.remove("overflowed"),e}())}(window,navigator,document);
