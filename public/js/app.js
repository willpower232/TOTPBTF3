!function(){if(void 0!==window.refreshat)var e=document.querySelector(".a-timer"),t=setInterval(function(){var r=Math.floor((30-(window.refreshat-Date.now()/1e3))/30*100);r>=100?(e.style.opacity=0,clearInterval(t),setTimeout(function(){window.location.reload()},100)):e&&(e.style.setProperty("--progress",r),e.style.opacity=1)},1e3);(toggle=document.querySelector('input[name="light_mode"]'))&&toggle.addEventListener("change",function(e){var t=new XMLHttpRequest,r=new FormData;r.append("light_mode",toggle.checked),t.open("POST","/api/profile/setLightMode"),t.setRequestHeader("X-Requested-With","XMLHttpRequest"),t.setRequestHeader("Accept","application/json"),t.setRequestHeader("X-CSRF-TOKEN",document.querySelector('meta[name="csrf-token"]').getAttribute("content")),t.onload=function(){var e=this.responseText;if(200==this.status)try{e=JSON.parse(e),!0===e.current_state?document.body.parentNode.classList.add("inverted"):!1===e.current_state&&document.body.parentNode.classList.remove("inverted")}catch(e){alert("Not received JSON")}else alert("Received: "+this.status)},t.send(r)}),"clipboard"in navigator&&(copybutton=document.querySelector(".js-copy"))&&(copybutton.classList.add("enabled"),copybutton.addEventListener("click",function(e){var t=document.querySelector(e.target.dataset.copies).innerText;navigator.clipboard.writeText(t).then(function(){copybutton.classList.add("success")}).catch(function(e){copybutton.classList.add("failure"),console.log(e)}).finally(function(){setTimeout(function(){copybutton.classList.remove("success","failure")},800)})})),(breadcrumbs=document.querySelector(".breadcrumbs"))&&window.addEventListener("resize",function e(){var t=breadcrumbs.firstElementChild,r=breadcrumbs.lastElementChild,o=breadcrumbs.offsetWidth;return r.offsetLeft+r.offsetWidth>o?breadcrumbs.classList.add("overflowed"):t.offsetLeft>0&&breadcrumbs.classList.remove("overflowed"),e}()),"serviceWorker"in navigator&&navigator.serviceWorker.register("/service-worker.js")}();