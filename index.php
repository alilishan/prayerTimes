<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Prayer Times</title>
	<link rel="stylesheet" href="assets/libs/bootstrap-3.3.5/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/css/plugin-main.css">

	<script src="assets/libs/jquery-1.11.2.min.js"></script>
	<script src="assets/plugins/PrayTimes.js"></script>
	<script src="assets/plugins/moment/moment.min.js"></script>
	<script src="assets/plugins/moment/moment-hijri.js"></script>

	<script>

	var LATITUDE = 3.0474959, 	//Specifies the coordinates of the input location as a triple [latitude, longitude, elevation]
		LONGITUDE = 101.6884195,
		TIMEZONE = 8, 			//The difference to Greenwich time (GMT) in hours. If omitted or set to 'auto', timezone is extracted from the system.
		pLIST = ['Fajr',  'Dhuhr', 'Asr', 'Maghrib', 'Isha', ]; //'Sunrise','Midnight'

	$(document).ready(function(){

		//navigator.geolocation.getCurrentPosition(showPosition);

		var pTIMES = prayTimes.getTimes(new Date(), [LATITUDE, LONGITUDE], TIMEZONE);
		//console.log(pTIMES)

		//Initialize
		updatesDates();
		updateNextPrayer(pLIST, pTIMES);
		updateCurrentPrayer(pLIST, pTIMES);
		updateTodaysPrayers(pLIST, pTIMES);

		setInterval(function() {
			updateNextPrayer(pLIST, pTIMES);
		}, 1000);

		//END Doc Ready
	});
	
	function showPosition(position) {
		console.log("Latitude: " + position.coords.latitude);
		console.log("Longitude: " + position.coords.longitude); 
	}

	function getCurrentPrayer(listArray, timesArray){
		var nowString = moment().format('YYYY-MM-DD HH:mm'),
			nowDate = moment().format('YYYY-MM-DD'),
			returnPrayer = false;

		for (var i = 0; i < listArray.length; i++) {
			var pryrTime = nowDate+' '+timesArray[listArray[i].toLowerCase()];
			
			if(moment(nowString).isBefore(pryrTime, 'seconds')){
				returnPrayer = listArray[i - 1];
				break;
			}
		};

		return returnPrayer;
	}

	function getNextPrayer(listArray, timesArray){
		var nowString = moment().format('YYYY-MM-DD HH:mm'),
			nowDate = moment().format('YYYY-MM-DD'),
			returnPrayer = false;

		for (var i = 0; i < listArray.length; i++) {
			var pryrTime = nowDate+' '+timesArray[listArray[i].toLowerCase()];
			if(moment(nowString).isBefore(pryrTime, 'seconds')){
				returnPrayer = listArray[i];
				break;
			}
			//console.log(' --> '+listArray[i]+' -> '+moment(nowString).isBefore(pryrTime, 'seconds')); //console.log(nowString+' '+pryrTime);
		};

		return returnPrayer;
	}

	function getTimeToPrayer(prayer, timesArray){
		var nowTime = moment().format('HH:mm:ss'),
			prayerTime = timesArray[prayer.toLowerCase()]+':00';
		
		//console.log(nowTime, prayerTime);	
		return moment.utc(moment(prayerTime,"HH:mm:ss").diff(moment(nowTime,"HH:mm:ss"))).format("HH:mm:ss");
	}

	function getConvertedTime(prayerTime){
		var convetedTime = moment(prayerTime, 'HH:mm')
							.format('hh:mm')
							.trim()
							.split(':'); 
							//console.log(prayerTime);
							//console.log(prayerTime.split(':')[0] > 12)
		var ampm = (parseInt(prayerTime.split(':')[0]) > 12) ? 'pm' : 'am';

		return {time: convetedTime, ampm: ampm}
	}

	function updateCurrentPrayer(pLIST, pTIMES){
		var currentPrayer = getCurrentPrayer(pLIST, pTIMES);
		
		if(!currentPrayer){
			currentPrayer = pLIST[pLIST.length - 1]; //Get Last Prayer

			//Check If Yesterday or Before
			var nowString = moment().format('YYYY-MM-DD HH:mm'),
				tomString = moment().subtract('days', 1).format('YYYY-MM-DD')+' 00:00:00';

			if(moment(nowString).isAfter(tomString, 'seconds')){ 
				pTIMES = prayTimes.getTimes(moment().subtract('days', 1)._d, [LATITUDE, LONGITUDE], TIMEZONE);
				//console.log('sPrayer Taking Yesterday\'s Isha.')
			}
		}

		var prayerTime = pTIMES[currentPrayer.toLowerCase()],
			convetedTime = getConvertedTime(prayerTime);

		$('#current-prayer .prayer-name').text(currentPrayer);
		$('#current-prayer .time-hour').text(convetedTime.time[0]);
		$('#current-prayer .time-min').text(convetedTime.time[1]);
		$('#current-prayer .time-ampm').text(convetedTime.ampm); 

		return false;
	}

	function updateNextPrayer(pLIST, pTIMES){
		var nextPRAYER = getNextPrayer(pLIST, pTIMES);

		if(!nextPRAYER){
			nextPRAYER = pLIST[0]; //Tomorrows First Prayer

			//Check If Tomorrow After
			var nowString = moment().format('YYYY-MM-DD HH:mm'),
				tomString = moment().add('days', 1).format('YYYY-MM-DD')+' 00:00:00';

			if(moment(nowString).isBefore(tomString, 'seconds')){
				pTIMES = prayTimes.getTimes(moment().add('days', 1)._d, [LATITUDE, LONGITUDE], TIMEZONE); //Tomorrows prayer times
				//console.log('sMap Taking Tomorrow\'s Fajr');
			}	

		}

		var timeToPrayer = getTimeToPrayer(nextPRAYER, pTIMES),
			prayerTime = pTIMES[nextPRAYER.toLowerCase()],
			convetedTime = getConvertedTime(prayerTime),
			splitTimeToPrayer = timeToPrayer.split(':');

		if($('#prayer-elapsed-time .prayer-name').text().toLowerCase() != nextPRAYER.toLowerCase()){
			updateCurrentPrayer(pLIST, pTIMES);
		} 

		$('#next-prayer .prayer-name').text(nextPRAYER);
		$('#next-prayer .time-hour').text(convetedTime.time[0]);
		$('#next-prayer .time-min').text(convetedTime.time[1]);
		$('#next-prayer .time-ampm').text(convetedTime.ampm);

		$('#prayer-elapsed-time .time-hour').text(splitTimeToPrayer[0]);
		$('#prayer-elapsed-time .time-min').text(splitTimeToPrayer[1]);
		$('#prayer-elapsed-time .time-sec').text(splitTimeToPrayer[2]);

		$('#prayer-elapsed-time .prayer-name').text(nextPRAYER);

		return false;
	}

	function updatesDates(){
		var day = moment().format('dddd');
		var date = moment().format('DD MMMM, YYYY');
		var hijr = moment().format('iDD iMMMM, iYYYY');

		$('#heading-today').text(day);
		$('#heading-date-eng').text(date);
		$('#heading-date-hjr').text(hijr);
	}

	function updateTodaysPrayers(pLIST, pTIMES){
		var $target = $('#todaysPrayerTimes');

		for(var i in pLIST){
			var template = '<li><div class="row">';
					template += '<div class="col-xs-6">'+pLIST[i]+'</div>';
					template += '<div class="col-xs-6 text-right">'+pTIMES[pLIST[i].toLowerCase()]+'</div>';
				template += '</div></li>';

			$(template).appendTo($target);	
		}

		return false;
	}

	</script>
</head>
<body>

	<h3 class="heading-for-smallbox">Prayer Times</h3>

	<div class="container-fluid">
		<div class="text-center">
			<div id="heading-today" class="heading-today">Today Friday</div>
			<div id="heading-date-eng" class="heading-dates">26 July, 2015</div>
			<div id="heading-date-hjr" class="heading-dates">17 Ramadan, 1440</div>
			
			<div id="current-prayer" title="Time to Pray" class="current-prayer">
				<div class="text">
					<span class="time-hour">11</span>
					<span class="time-blink">:</span>
					<span class="time-min">54</span>
					<span class="time-ampm">AM</span>
				</div>
				<div class="text prayer-name">Duhur</div>
			</div>

			<div id="next-prayer" title="Next Prayer" class="next-prayer">
				<div>
					<span class="text prayer-name">Duhur</span>
					<span class="text spacer">at</span>
					<span class="text">
						<span class="time-hour">11</span>
						<span class="time-blink">:</span>
						<span class="time-min">54</span>
						<span class="time-ampm">AM</span>
					</span>
				</div>
			</div>

			<div id="prayer-elapsed-time" class="prayer-elapsed-time">
				<div id="elapsed-time" class="elapsed-time">
					<span class="time-hour">01</span>
					<span class="time-blink blink">:</span>
					<span class="time-min">20</span>
					<span class="time-blink blink">:</span>
					<span class="time-sec">54</span>
				</div>
				<div class="till-athan">Time Till <span class="prayer-name"></span></div>
			</div>

		</div>

		<div>
			<ul id="todaysPrayerTimes">
				
			</ul>	
		</div>

	</div>

	
</body>
</html>