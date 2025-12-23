/* Bengali (বাংলা) initialisation for the jQuery UI date picker plugin. */
jQuery(function($){
$.datepicker.regional['bn'] = {
closeText: 'বন্ধ',
prevText: 'আগে',
nextText: 'পরে',
currentText: 'আজ',
monthNames: ['জানুয়ারী','ফেব্রুয়ারী','মার্চ','এপ্রিল','মে','জুন',
'জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'],
monthNamesShort: ['জানু','ফেব','মার্চ','এপ্র','মে','জুন',
'জুল','আগ','সেপ','অক্টো','নভে','ডিসে'],
dayNames: ['রবিবার','সোমবার','মঙ্গলবার','বুধবার','বৃহস্পতিবার','শুক্রবার','শনিবার'],
dayNamesShort: ['রবি','সোম','মঙ্গল','বুধ','বৃহস্পতি','শুক্র','শনি'],
dayNamesMin: ['রবি','সোম','মঙ্গল','বুধ','বৃহ','শুক্র','শনি'],
weekHeader: 'সপ্তাহ',
dateFormat: 'dd/mm/yy',
firstDay: 0,
isRTL: false,
showMonthAfterYear: false,
yearSuffix: ''
};
$.datepicker.setDefaults($.datepicker.regional['bn']);
});
