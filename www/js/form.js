

$(document).ready(function () {

	var actualIndex;
	//var arrvisible = [];
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
		//arrvisible[i] = false;
		rotate[i] = false;
		arrRotStartX[i] = 0;
		arrRotStartY[i] = 0;
		mousedown[i] = false;
		arrDownStartX[i] = 0;
		arrDownStartPosX[i] = 0;
		arrDownStartY[i] = 0;
		arrAngel[i] = 0;
		moveToPositionAndRotate(i);
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

	$('#image-wrap').mousedown(function (e) {
		rotate[actualIndex] = true;
		arrRotStartX[actualIndex] = e.pageX - this.offsetLeft;
		arrRotStartY[actualIndex] = e.pageY - this.offsetTop;
		arrAngel[i] = 0;
		saveStart(actualIndex, arrRotStartX[actualIndex], arrRotStartY[actualIndex]);
	});

	$('#image-wrap').mouseup(function (e) {
		if (arrRotStartX[actualIndex] === e.pageX - this.offsetLeft &&
				arrRotStartY[actualIndex] === e.pageY - this.offsetTop
				) {
			deleteArrow(actualIndex);
		}
		rotate[actualIndex] = false;
	});

	$('#image-wrap').mousemove(function (e) {
		if (rotate[actualIndex]) {
			var x = e.pageX - this.offsetLeft;
			var y = e.pageY - this.offsetTop;

			var xRtt = Math.atan2(arrRotStartY[actualIndex] - y, arrRotStartX[actualIndex] - x);
			//var xDeg = (xRtt > 0 ? x : (2 * Math.PI + xRtt)) * 360 / (2 * Math.PI)
			var xDeg = (xRtt * 180 / Math.PI) - 90;
			xDeg = xDeg < 0 ? 360 + xDeg : xDeg;
			arrAngel[i] = xDeg;
			saveAngle(actualIndex, xDeg);
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

	$("#frm-birdForm-body").change(function (e) {
		$("#body-info .value").text($(this).val());
		saveAngle(1, $(this).val());		
	});

	$("#frm-birdForm-head").change(function (e) {
		$("#head-info .value").text($(this).val());
		saveAngle(2, $(this).val());		
	});


	function setIndex(i) {
		actualIndex = i;
		frameButtons(i);
	}
});

function frameButtons(i) {
	if (i === 1) {
		$('#body-info').css({"border": "solid"});
		$('#head-info').css({"border": "none"});
	}
	if (i === 2) {
		$('#head-info').css({"border": "solid"});
		$('#body-info').css({"border": "none"});
	}
}

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
	moveToPositionAndRotate(index);
}

function deleteArrow(index) {
	var selectorx = "";
	var selectory = "";
	var selectorAngle = "";
	if (index === 1) {
		selectorx = "#frm-birdForm-bodyx";
		selectory = "#frm-birdForm-bodyy";
		selectorAngle = "#frm-birdForm-body";
	}
	if (index === 2) {
		selectorx = "#frm-birdForm-headx";
		selectory = "#frm-birdForm-heady";
		selectorAngle = "#frm-birdForm-head";
	}
	$(selectorx).val("");
	$(selectory).val("");
	$(selectorAngle).val("");
	moveToPositionAndRotate(index);
}



function saveAngle(i, angle) {
	var selectorForm = "";
	var selectorDiv = "";
	if (i === 1) {
		selectorForm = "#frm-birdForm-body";
		selectorDiv = "#body-info .value";
	}
	if (i === 2) {
		selectorForm = "#frm-birdForm-head";
		selectorDiv = "#head-info .value";
	}
	var roundDeg = Math.round(angle);
	roundDeg = roundDeg % 360;

	$(selectorForm).val(roundDeg);
	$(selectorDiv).text(roundDeg + "°");
	rotateItem(i)
}

function moveToPositionAndRotate(i) {
	var selectorx = "";
	var selectory = "";
	if (i === 1) {
		selectorx = "#frm-birdForm-bodyx";
		selectory = "#frm-birdForm-bodyy";
	}
	if (i === 2) {
		selectorx = "#frm-birdForm-headx";
		selectory = "#frm-birdForm-heady";
	}
	if ($(selectorx).val() === "" || $(selectory).val() === "") {
		$('#pointer-wrap-' + i).css({
			"display": "none"
		});
	} else {
		var x = $(selectorx).val() - ($('#pointer-wrap-' + i).width() / 2);
		var y = $(selectory).val() - ($('#pointer-wrap-' + i).height() / 2);
		$('#pointer-wrap-' + i).css({
			"display": "block",
			"left": x + "px",
			"top": y + "px",
		});
	}
	rotateItem(i);
}

function rotateItem(i) {
	var selectorForm = "";
	if (i === 1) {
		selectorForm = "#frm-birdForm-body";
	}
	if (i === 2) {
		selectorForm = "#frm-birdForm-head";
	}
	var roundDeg = $(selectorForm).val();
	if (roundDeg !== "") {
		$('#pointer-wrap-' + i).css({
			"-moz-transform": "rotate(" + roundDeg + "deg)",
			"-webkit-transform": "rotate(" + roundDeg + "deg)",
			"transform": "rotate(" + roundDeg + "deg)"
		});
	}
}
