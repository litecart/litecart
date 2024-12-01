// Dropdown

$('.dropdown [data-toggle="dropdown"]').on('click', function(e) {
	$(this).closest('.dropdown').toggleClass('open')
})

$('.dropdown').on('focusout', function(e) {
	$(this).closest('.dropdown').removeClass('open')
})

$('.dropdown').on('click', 'a', function(e) {
	$(this).closest('.dropdown').removeClass('open')
})
