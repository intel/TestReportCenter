function addDateSelector(elem) {

	elem.datepicker({
		showOn: "both",
		buttonImage: "/images/calendar_icon.png",
		buttonImageOnly: true,
		firstDay: 1,
		selectOtherMonths: true,
		dateFormat: "yy-mm-dd"
	});

	//var date = new Date(Date.parse(elem.val()));
	//var dateStr = date.getUTCFullYear() + '-' + (date.getUTCMonth()+1) + '-' + date.getUTCDate();
	//elem.val(dateStr);  
}
