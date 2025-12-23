/* Turkish initialisation for the jQuery UI date picker plugin. */
/* Written by Cemil YILDIZ */
(function(factory) {
"use strict";
if (typeof define === "function" && define.amd) {
define(["../widgets/datepicker"], factory);
} else {
factory(jQuery.datepicker);
}
}(function(datepicker) {
"use strict";
datepicker.regional.tr = {
closeText: "kapat",
prevText: "geri",
nextText: "ileri",
currentText: "bugün",
monthNames: ["Ocak","Şubat","Mart","Nisan","Mayıs","Haziran",
"Temmuz","Ağustos","Eylül","Ekim","Kasım","Aralık"],
monthNamesShort: ["Oca","Şub","Mar","Nis","May","Haz",
"Tem","Ağu","Eyl","Eki","Kas","Ara"],
dayNames: ["Pazar","Pazartesi","Salı","Çarşamba","Perşembe","Cuma","Cumartesi"],
dayNamesShort: ["Pz","Pt","Sa","Ça","Pe","Cu","Ct"],
dayNamesMin: ["Pz","Pt","Sa","Ça","Pe","Cu","Ct"],
weekHeader: "Hf",
dateFormat: "dd.mm.yy",
firstDay: 1,
isRTL: false,
showMonthAfterYear: false,
yearSuffix: ""
};
datepicker.setDefaults(datepicker.regional.tr);
return datepicker.regional.tr;
}));
