!function(){if(void 0!==window.refreshat)var e=setInterval(function(){window.refreshat<Date.now()/1e3&&(clearInterval(e),setTimeout(function(){window.location.reload()},500))},1e3);(toggle=document.querySelector('input[name="light_mode"]'))&&toggle.addEventListener("change",function(e){var t=new XMLHttpRequest,r=new FormData;r.append("light_mode",toggle.checked),t.open("POST","/api/profile/setLightMode"),t.setRequestHeader("X-Requested-With","XMLHttpRequest"),t.setRequestHeader("Accept","application/json"),t.setRequestHeader("X-CSRF-TOKEN",document.querySelector('meta[name="csrf-token"]').getAttribute("content")),t.onload=function(){var e=this.responseText;if(200==this.status)try{e=JSON.parse(e),!0===e.current_state?document.body.parentNode.classList.add("inverted"):!1===e.current_state&&document.body.parentNode.classList.remove("inverted")}catch(e){alert("Not received JSON")}else alert("Received: "+this.status)},t.send(r)}),"serviceWorker"in navigator&&navigator.serviceWorker.register("./service-worker.js")}();