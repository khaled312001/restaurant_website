/* Hindi (हिन्दी) initialisation for the jQuery UI date picker plugin. */
jQuery(function($){
    $.datepicker.regional['hi'] = {
        closeText: 'बंद करें',
        prevText: 'पिछला',
        nextText: 'अगला',
        currentText: 'आज',
        monthNames: ['जनवरी','फ़रवरी','मार्च','अप्रैल','मई','जून',
        'जुलाई','अगस्त','सितंबर','अक्टूबर','नवंबर','दिसंबर'],
        monthNamesShort: ['जन','फर','मार्च','अप्रै','मई','जून',
        'जुल','अग','सित','अक्ट','नव','दिस'],
        dayNames: ['रविवार','सोमवार','मंगलवार','बुधवार','गुरुवार','शुक्रवार','शनिवार'],
        dayNamesShort: ['रवि','सोम','मंगल','बुध','गुरु','शुक्र','शनि'],
        dayNamesMin: ['र','सो','मं','बु','गु','शु','श'],
        weekHeader: 'सप्ताह',
        dateFormat: 'dd/mm/yy',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
    $.datepicker.setDefaults($.datepicker.regional['hi']);
});
