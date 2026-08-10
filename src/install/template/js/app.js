window.waitFor = window.waitFor || ((i,o)=>{void 0!==window.i?o(window.i):setTimeout((()=>waitFor(i,o)),50)});

(function(){
	var m = window.matchMedia('(prefers-color-scheme: dark)');
	var apply = function(e){
		document.documentElement.classList.toggle('dark-mode', e.matches);
	};
	if (m) {
		apply(m);
		m.addEventListener('change', apply);
	}
})();
