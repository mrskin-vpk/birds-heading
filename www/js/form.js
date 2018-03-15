$(document).ready(function () {
	//http://jsfiddle.net/HeFqh/
	var arrvisible = false;
	var rotate = true;

	var arrRotStartX = 0;
	var arrRotStartY = 0;

	var mousedown = false;
	var arrDownStartX = 0;
	var arrDownStartPosX = 0;
	var arrDownStartY = 0;


	//  Looking for a formula that will set the initial arrow rotation to point  to the left border of the target-box at any browser width. This will be  the target-box cursor x=0 position.         

	// This is just a wild guess at a formula that obviously doesn't work properly


	$('#image-wrap').dblclick(function (e) {
		arrvisible = false;
		rotate = true;
		$('#pointer-wrap').css({
			"display": "none",
		});
	});

	$('#image-wrap').click(function (e) {
		if (!arrvisible) {
			arrRotStartX = e.pageX - this.offsetLeft;
			arrRotStartY = e.pageY - this.offsetTop;

			var x = arrRotStartX - ($('#pointer-wrap').width() / 2);
			var xCoords = "(  arrStartX = " + x + " )";

			var y = arrRotStartY - ($('#pointer-wrap').height() / 2);
			var yCoords = "(  arrStartY = " + y + " )";

			$('span#xCoordinates2').text(xCoords + yCoords);


			$('#pointer-wrap').css({
				"display": "block",
				"left": x + "px",
				"top": y + "px",
				"-moz-transform": "rotate(0deg)",
				"-webkit-transform": "rotate(0deg)",
				"transform": "rotate(0deg)"
			});
			rotate = true;
			arrvisible = true;
		} else {
			rotate = false;
			arrvisible = false;
		}



	});

//		$('#pointer-wrap').css({
//			"-moz-transform": "rotate(" + startingRotatePosition + "deg)",
//			"-webkit-transform": "rotate(" + startingRotatePosition + "deg)",
//			"transform": "rotate(" + startingRotatePosition + "deg)"
//		});

//	$('#image-wrap').mousedown(function (e) {
//		mousedown = true;
//		arrDownStartX = e.pageX - this.offsetLeft;
//		arrDownStartPosX = -999999;
//		arrDownStartY = e.pageY - this.offsetTop;
//
//	});
//
//	$('#image-wrap').mouseup(function (e) {
//		mousedown = false;
//		arrDownStartPosX = -999999;
//	});


	$('#image-wrap').mousemove(function (e) {
		if (arrvisible && rotate) {
			var x = e.pageX - this.offsetLeft;
			var xCoords = "(  x = " + x + " )";
			var y = e.pageY - this.offsetTop;
			var yCoords = "(  y = " + y + " )";

//			if (mousedown) {
//				if (arrDownStartPosX == -999999) {
//					arrDownStartPosX = $('#pointer-wrap').position().left
//				}
//				var left = (x - arrDownStartX) + arrDownStartPosX;
//				var top = 0;
//				$('span#xCoordinates3').text("(left:" + (left) + ")");
//				$('#pointer-wrap').css({
//					"left": left,
//				});
//			} else {

			var xRtt = Math.atan2(arrRotStartY - y, arrRotStartX - x);
			//var xDeg = (xRtt > 0 ? x : (2 * Math.PI + xRtt)) * 360 / (2 * Math.PI)
			var xDeg = (xRtt * 180 / Math.PI) - 90;
			xDeg = xDeg < 0 ? 360 + xDeg : xDeg;

			$('#pointer-wrap').css({
				"-moz-transform": "rotate(" + xDeg + "deg)",
				"-webkit-transform": "rotate(" + xDeg + "deg)",
				"transform": "rotate(" + xDeg + "deg)"
			});

			var roundDeg = Math.round(xDeg);
			roundDeg = roundDeg % 360

			$('span#xCoordinates').text(xCoords + yCoords + "(" + roundDeg + "deg)");


		}

	});


});