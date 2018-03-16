

$(document).ready(function () {

	var actualIndex;
	//var arrvisible = [];
	var rotate = [];

	var arrRotStartX = [];
	var arrRotStartY = [];


	//http://jsfiddle.net/HeFqh/
	for (i = 1; i <= 2; i++) {
		//arrvisible[i] = false;
		rotate[i] = false;
		arrRotStartX[i] = 0;
		arrRotStartY[i] = 0;
		moveToPositionAndRotate(i);
	}
	$("#bird-info .heading").text($("#frm-birdForm-bird").val());

	setIndex(2);

	$("#body-info").click(function (e) {
		setIndex(1);
	});
	$("#head-info").click(function (e) {
		setIndex(2);
	});


	$("#same-info").click(function (e) {
		if (getAngle(1) !== "") {
			saveStart(2, getXValue(1) * 1 + 2, getYValue(1) * 1 + 2);
			saveAngle(2, getAngle(1));
		} else if (getAngle(2) !== "") {
			saveStart(1, getXValue(2) * 1 + 2, getYValue(2) * 1 + 2);
			saveAngle(1, getAngle(2));
		}
	});


	//  Looking for a formula that will set the initial arrow rotation to point  to the left border of the target-box at any browser width. This will be  the target-box cursor x=0 position.         

	// This is just a wild guess at a formula that obviously doesn't work properly

	$('#image-wrap').click(function (e) {
		if (rotate[actualIndex]) {
			rotate[actualIndex] = false;
		} else {
			rotate[actualIndex] = false;
			arrRotStartX[actualIndex] = e.pageX - this.offsetLeft;
			arrRotStartY[actualIndex] = e.pageY - this.offsetTop;
			saveStart(actualIndex, arrRotStartX[actualIndex], arrRotStartY[actualIndex]);
			rotate[actualIndex] = true;
		}
	});

//	$('#image-wrap').mouseup(function (e) {
//	deleteArrow(actualIndex);
//		if (arrRotStartX[actualIndex] === e.pageX - this.offsetLeft &&
//				arrRotStartY[actualIndex] === e.pageY - this.offsetTop
//				) {
//			deleteArrow(actualIndex);
//		}
//		rotate[actualIndex] = false;
//	});

	$('#image-wrap').mousemove(function (e) {
		console.log("x", e.pageX - this.offsetLeft);
		console.log("y", e.pageY - this.offsetTop);

		if (rotate[actualIndex]) {
			//console.log("rotating");
			var x = e.pageX - this.offsetLeft;
			var y = e.pageY - this.offsetTop;

			var xRtt = Math.atan2(arrRotStartY[actualIndex] - y, arrRotStartX[actualIndex] - x);
			//var xDeg = (xRtt > 0 ? x : (2 * Math.PI + xRtt)) * 360 / (2 * Math.PI)
			var xDeg = (xRtt * 180 / Math.PI) - 90;
			xDeg = xDeg < 0 ? 360 + xDeg : xDeg;
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
		saveAngle(1, $(this).val());
	});

	$("#frm-birdForm-head").change(function (e) {
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
		$('#image-wrap').removeClass("cursor-2");
		$('#image-wrap').addClass("cursor-1");
	}
	if (i === 2) {
		$('#head-info').css({"border": "solid"});
		$('#body-info').css({"border": "none"});
		$('#image-wrap').removeClass("cursor-1");
		$('#image-wrap').addClass("cursor-2");
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
	var xValue = getXValue(i);
	var yValue = getYValue(i);
	if (xValue === "" || yValue === "") {
		$('#pointer-wrap-' + i).css({
			"display": "none"
		});
	} else {
		var x = xValue - ($('#pointer-wrap-' + i).width() / 2);
		var y = yValue - ($('#pointer-wrap-' + i).height() / 2);
		$('#pointer-wrap-' + i).css({
			"display": "block",
			"left": x + "px",
			"top": y + "px",
		});
	}
	rotateItem(i);
}

function getXValue(i) {
	var selectorx = "";
	if (i === 1) {
		selectorx = "#frm-birdForm-bodyx";
	}
	if (i === 2) {
		selectorx = "#frm-birdForm-headx";
	}
	return $(selectorx).val();
}

function getYValue(i) {
	var selectory = "";
	if (i === 1) {
		selectory = "#frm-birdForm-bodyy";
	}
	if (i === 2) {
		selectory = "#frm-birdForm-heady";
	}
	return $(selectory).val();
}

function rotateItem(i) {
	var selectorDiv = "";
	if (i === 1) {
		selectorDiv = "#body-info .value";
	}
	if (i === 2) {
		selectorDiv = "#head-info .value";
	}
	var roundDeg = getAngle(i);
	if (roundDeg !== "") {
		$(selectorDiv).text(roundDeg + "°");
		$('#pointer-wrap-' + i).css({
			"-moz-transform": "rotate(" + roundDeg + "deg)",
			"-webkit-transform": "rotate(" + roundDeg + "deg)",
			"transform": "rotate(" + roundDeg + "deg)"
		});
	} else {
		$(selectorDiv).text("");
	}
}

function getAngle(i) {
	var selectorForm = "";
	if (i === 1) {
		selectorForm = "#frm-birdForm-body";
	}
	if (i === 2) {
		selectorForm = "#frm-birdForm-head";
	}
	return $(selectorForm).val();
}