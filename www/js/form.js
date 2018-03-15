var actualIndex;

$(document).ready(function () {

	var arrvisible = [];
	var rotate = [];

	var arrRotStartX = [];
	var arrRotStartY = [];

	var mousedown = [];
	var arrDownStartX = [];
	var arrDownStartPosX = [];
	var arrDownStartY = [];

	var arrAngel = [];


	//http://jsfiddle.net/HeFqh/
	for (i = 1; i <= 2; i++) {
		arrvisible[i] = false;
		rotate[i] = true;
		arrRotStartX[i] = 0;
		arrRotStartY[i] = 0;
		mousedown[i] = false;
		arrDownStartX[i] = 0;
		arrDownStartPosX[i] = 0;
		arrDownStartY[i] = 0;
		arrAngel[i] = 0;
	}

	setIndex(1);

	$("#body-info").click(function (e) {
		setIndex(1);
	});
	$("#head-info").click(function (e) {
		setIndex(2);
	});

//
//	$("#same-info").click(function (e) {
//		if (!arrvisible[1]) {
//			return;
//		}
//		setPositionAndRotation(2, "block", arrRotStartX[1] + 1, arrRotStartY[1] + 1, arrAngel[1]);
//	});


	//  Looking for a formula that will set the initial arrow rotation to point  to the left border of the target-box at any browser width. This will be  the target-box cursor x=0 position.         

	// This is just a wild guess at a formula that obviously doesn't work properly

	$('#image-wrap').click(function (e) {
		if (!arrvisible[actualIndex]) {
			arrRotStartX[actualIndex] = e.pageX - this.offsetLeft;
			arrRotStartY[actualIndex] = e.pageY - this.offsetTop;

			var x = arrRotStartX[actualIndex] - ($('#pointer-wrap-' + actualIndex).width() / 2);
			var y = arrRotStartY[actualIndex] - ($('#pointer-wrap-' + actualIndex).height() / 2);
			arrAngel[i] = 0;
			setPositionAndRotation(actualIndex, "block", x, y, 0);

			rotate[actualIndex] = true;
			arrvisible[actualIndex] = true;
		} else {
			rotate[actualIndex] = false;
			arrvisible[actualIndex] = false;
		}
		saveStart(actualIndex, arrRotStartX[actualIndex], arrRotStartY[actualIndex]);
	});

	$('#image-wrap').mousemove(function (e) {
		if (arrvisible[actualIndex] && rotate[actualIndex]) {
			var x = e.pageX - this.offsetLeft;
			var y = e.pageY - this.offsetTop;

			var xRtt = Math.atan2(arrRotStartY[actualIndex] - y, arrRotStartX[actualIndex] - x);
			//var xDeg = (xRtt > 0 ? x : (2 * Math.PI + xRtt)) * 360 / (2 * Math.PI)
			var xDeg = (xRtt * 180 / Math.PI) - 90;
			xDeg = xDeg < 0 ? 360 + xDeg : xDeg;
			arrAngel[i] = xDeg;
			setRotation(actualIndex, xDeg);
		}

	});

	$(".img-wrap").click(function (e) {
		$(".spices .img-wrap").css({"border-color": "black"});
		$(this).css({"border-color": "red"});
		$("#frm-birdForm-bird").val($(this).children(".img-name").text());
		$("#bird-info .heading").text($("#frm-birdForm-bird").val());
	});

	$("#frm-birdForm-bird").change(function (e) {
		$("#bird-info .heading").text($(this).val());
	});
	
	$("#frm-birdForm-head").change(function (e) {
		$("#head-info .value").text($(this).val());
	});

	$("#frm-birdForm-body").change(function (e) {
		$("#body-info .value").text($(this).val());
	});
	

});

function saveStart(index, x, y) {
	var selectorx = "";
	var selectory = "";
	if (index === 1) {
		selectorx = "#frm-birdForm-bodyx";
		selectory = "#frm-birdForm-bodyy";
	}
	if (index === 2) {
		selectorx = "#frm-birdForm-headx";
		selectory = "#frm-birdForm-heady";
	}
	$(selectorx).val(x);
	$(selectory).val(y);
}

function saveAngle(index, angle) {
	var selectorForm = "";
	var selectorDiv = "";
	if (index === 1) {
		selectorForm = "#frm-birdForm-body";
		selectorDiv = "#body-info .value";
	}
	if (index === 2) {
		selectorForm = "#frm-birdForm-head";
		selectorDiv = "#head-info .value";
	}
	$(selectorForm).val(angle);
	$(selectorDiv).text(angle + "°");
}

function setIndex(i) {
	actualIndex = i;
	//$('span#xCoordinates').text("index: " + i);
	if (i === 1) {
		$('#body-info').css({"border": "solid"});
		$('#head-info').css({"border": "none"});
	}
	if (i === 2) {
		$('#head-info').css({"border": "solid"});
		$('#body-info').css({"border": "none"});
	}
}

function setPositionAndRotation(i, display, x, y, rotation) {
	$('#pointer-wrap-' + i).css({
		"display": display,
		"left": x + "px",
		"top": y + "px",
	});
	setRotation(i, rotation);
}

function setRotation(i, xDeg) {
	$('#pointer-wrap-' + i).css({
		"-moz-transform": "rotate(" + xDeg + "deg)",
		"-webkit-transform": "rotate(" + xDeg + "deg)",
		"transform": "rotate(" + xDeg + "deg)"
	});
	var roundDeg = Math.round(xDeg);
	roundDeg = roundDeg % 360;
	saveAngle(i, roundDeg);
}