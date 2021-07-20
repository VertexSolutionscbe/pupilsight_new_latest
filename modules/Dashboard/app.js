//let calendar = document.querySelector('.calendar')
var calendar = document.querySelector('.calendar')
//const month_names = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
var month_names = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']


isLeapYear = (year) => {
    return (year % 4 === 0 && year % 100 !== 0 && year % 400 !== 0) || (year % 100 === 0 && year % 400 ===0)
}

getFebDays = (year) => {
    return isLeapYear(year) ? 29 : 28
}

generateCalendar = (month, year) => {

    var calendar_days = calendar.querySelector('.calendar-days')
    var calendar_header_year = calendar.querySelector('#year')

    var days_of_month = [31, getFebDays(year), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]

    calendar_days.innerHTML = ''

    var currDate = new Date()
    if (!month) month = currDate.getMonth()
    if (!year) year = currDate.getFullYear()

    var curr_month = `${month_names[month]}`
    month_picker.innerHTML = curr_month
    calendar_header_year.innerHTML = year

    // get first day of month
    
    var first_day = new Date(year, month, 1)

    for (var i = 0; i <= days_of_month[month] + first_day.getDay() - 1; i++) {
        var day = document.createElement('div')
        if (i >= first_day.getDay()) {
            day.classList.add('calendar-day-hover')
            day.innerHTML = i - first_day.getDay() + 1
            day.innerHTML += `<span></span>
                            <span></span>
                            <span></span>
                            <span></span>`
            if (i - first_day.getDay() + 1 === currDate.getDate() && year === currDate.getFullYear() && month === currDate.getMonth()) {
                day.classList.add('curr-date')
            }
			
			day.id="div"+(i - first_day.getDay() + 1)+"-"+curr_year.value+"-"+month;
            var divid="div"+(i - first_day.getDay() + 1);
            day.value=(i - first_day.getDay() + 1);
        }
        calendar_days.appendChild(day)
    }
	createclick();
}

var month_list = calendar.querySelector('.month-list')

month_names.forEach((e, index) => {
    var month = document.createElement('div')
    month.innerHTML = `<div data-month="${index}">${e}</div>`
    month.querySelector('div').onclick = () => {
        month_list.classList.remove('show')
        curr_month.value = index
        generateCalendar(index, curr_year.value)
    }
    month_list.appendChild(month)
})

var month_picker = calendar.querySelector('#month-picker')

month_picker.onclick = () => {
    month_list.classList.add('show')
	createclick();
}

var currDate = new Date()

var curr_month = {value: currDate.getMonth()}
var curr_year = {value: currDate.getFullYear()}

generateCalendar(curr_month.value, curr_year.value)

function createclick()
{
document.querySelector('#'+'div1-'+curr_year.value+"-"+curr_month.value).onclick = () => {
var cookieValue = document.getElementById('div1-'+curr_year.value+"-"+curr_month.value).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
document.querySelector('#'+'div2-'+curr_year.value+"-"+curr_month.value).onclick = () => {
var cookieValue = document.getElementById('div2-'+curr_year.value+"-"+curr_month.value).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
document.querySelector('#'+'div3-'+curr_year.value+"-"+curr_month.value).onclick = () => {
var cookieValue = document.getElementById('div3-'+curr_year.value+"-"+curr_month.value).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
document.querySelector('#'+'div4-'+curr_year.value+"-"+curr_month.value).onclick = () => {
var cookieValue = document.getElementById('div4-'+curr_year.value+"-"+curr_month.value).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
document.querySelector('#'+'div5-'+curr_year.value+"-"+curr_month.value).onclick = () => {
var cookieValue = document.getElementById('div5-'+curr_year.value+"-"+curr_month.value).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
document.querySelector('#'+'div6-'+curr_year.value+"-"+curr_month.value).onclick = () => {
var cookieValue = document.getElementById('div6-'+curr_year.value+"-"+curr_month.value).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
document.querySelector('#'+'div7-'+curr_year.value+"-"+curr_month.value).onclick = () => {
var cookieValue = document.getElementById('div7-'+curr_year.value+"-"+curr_month.value).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
document.querySelector('#'+'div8-'+curr_year.value+"-"+curr_month.value).onclick = () => {
var cookieValue = document.getElementById('div8-'+curr_year.value+"-"+curr_month.value).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
document.querySelector('#'+'div9-'+curr_year.value+"-"+curr_month.value).onclick = () => {
var cookieValue = document.getElementById('div9-'+curr_year.value+"-"+curr_month.value).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
document.querySelector('#'+'div10-'+curr_year.value+"-"+curr_month.value).onclick = () => {
var cookieValue = document.getElementById('div10-'+curr_year.value+"-"+curr_month.value).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
document.querySelector('#'+'div11-'+curr_year.value+"-"+curr_month.value).onclick = () => {
var cookieValue = document.getElementById('div11-'+curr_year.value+"-"+curr_month.value).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}

document.querySelector('#'+'div12-'+curr_year.value+"-"+curr_month.value).onclick = () => {
var cookieValue = document.getElementById('div12-'+curr_year.value+"-"+curr_month.value).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}

document.querySelector('#'+'div13-'+curr_year.value+"-"+curr_month.value).onclick = () => {
var cookieValue = document.getElementById('div13-'+curr_year.value+"-"+curr_month.value).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}

document.querySelector('#'+'div14-'+curr_year.value+"-"+curr_month.value).onclick = () => {
var cookieValue = document.getElementById('div14-'+curr_year.value+"-"+curr_month.value).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}

var vdiv15='div15-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv15).onclick = () => {
var cookieValue = document.getElementById(vdiv15).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
var vdiv16='div16-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv16).onclick = () => {
var cookieValue = document.getElementById(vdiv16).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
var vdiv17='div17-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv17).onclick = () => {
var cookieValue = document.getElementById(vdiv17).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
var vdiv18='div18-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv18).onclick = () => {
var cookieValue = document.getElementById(vdiv18).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
var vdiv19='div19-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv19).onclick = () => {
var cookieValue = document.getElementById(vdiv19).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
var vdiv20='div20-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv20).onclick = () => {
var cookieValue = document.getElementById(vdiv20).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
var vdiv21='div21-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv21).onclick = () => {
var cookieValue = document.getElementById(vdiv21).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
var vdiv22='div22-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv22).onclick = () => {
var cookieValue = document.getElementById(vdiv22).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
var vdiv23='div23-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv23).onclick = () => {
var cookieValue = document.getElementById(vdiv23).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
var vdiv24='div24-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv24).onclick = () => {
var cookieValue = document.getElementById(vdiv24).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
var vdiv25='div25-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv25).onclick = () => {
var cookieValue = document.getElementById(vdiv25).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
var vdiv26='div26-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv26).onclick = () => {
var cookieValue = document.getElementById(vdiv26).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
var vdiv27='div27-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv27).onclick = () => {
var cookieValue = document.getElementById(vdiv27).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
var vdiv28='div28-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv28).onclick = () => {
var cookieValue = document.getElementById(vdiv28).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
var vdiv29='div29-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv29).onclick = () => {
var cookieValue = document.getElementById(vdiv29).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
var vdiv30='div30-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv30).onclick = () => {
var cookieValue = document.getElementById(vdiv30).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
var vdiv31='div31-'+curr_year.value+"-"+curr_month.value;
document.querySelector('#'+vdiv31).onclick = () => {
var cookieValue = document.getElementById(vdiv31).value;    
alert(cookieValue+"-"+(curr_month.value+1)+"-"+curr_year.value);
}
}

createclick();
document.querySelector('#prev-year').onclick = () => {
    --curr_year.value
    generateCalendar(curr_month.value, curr_year.value)
	createclick();;
}

document.querySelector('#next-year').onclick = () => {
    ++curr_year.value
    generateCalendar(curr_month.value, curr_year.value)
	createclick();
}

var dark_mode_toggle = document.querySelector('.dark-mode-switch')
/*
dark_mode_toggle.onclick = () => {
    document.querySelector('body').classList.toggle('light')
    document.querySelector('body').classList.toggle('dark')
}*/