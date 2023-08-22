
document.body.setAttribute('hx-ext', 'head-support');
document.head.setAttribute('hx-head', 'merge');
htmx.process(document);
document.addEventListener('htmx:afterRequest', function(evt) {
	// console.log('completed!', document.querySelector('html'));
	const htmlElement = document.querySelector('html');
    htmlElement.style.overflow = 'unset';
	htmlElement.classList.remove('has-modal-open');
});