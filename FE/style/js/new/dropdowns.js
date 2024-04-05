$('.dropdown:not(.disabled) .dropdown-toggle').click(function (){
   $(this).closest('.dropdown').find('.dropdown-menu').slideToggle();
});


$('.dropdown:not(.disabled) .dropdown-item').click(function (e){
    e.preventDefault();
    $(this).closest('ul').find('.dropdown-item').removeClass('active').find('.icon-active').html('');
    $(this).addClass('active');
    loadDropdownActive($(this).closest('.dropdown'));
    $(this).closest('form').submit();
});

$('.dropdown').each(function (){
   loadDropdownActive($(this));
});

function loadDropdownActive(elParent){
        let el = elParent.find('.dropdown-item.active');
        if(el.length < 1 ){
           el = elParent.find('.dropdown-item').eq(0).addClass('active');
        }
        let val = el.data('value');
        $(elParent.data('id')).val(val);
        elParent.find('.dropdown-toggle').html(el.html());
        el.find('.icon-active').html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">\n' +
            '                                    <path d="M2.6665 6.66667L6.6665 10.6667L13.3332 4" stroke="#1A64F0" stroke-width="1.5" stroke-linecap="round"/>\n' +
            '                                </svg>');
        elParent.find('.dropdown-menu').slideUp();
}