<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="css/styles.css">
	<script type="text/javascript" src="js/lib/jq.min.js"></script>
	<script type="text/javascript" src="js/lib/delaunay.js"></script>
	<title>Триангуляция произвольно многоугольника</title>
</head>
<body>
	<div>
		<div id="message"></div>
		<span>you can drag a file into the dashed field</span>
		 <form name="uploadFileForm" enctype="multipart/form-data" method="POST">
        	<input name="userfile" type="file" id="fileField" class="fileField" /><br><br>
        	<button id="uploadFileButton" type="submit" name="submit">Upload file with coordinates</button>
    	</form>
		<br>
		<button id="clearContextButton">Clear context</button>
		<button id="splitFigureButton">Triangulate figure</button>
		<button id="calculateFigureAreaButton">Calculate Area</button>
		<button id="documentationButton">Open documentation</button>
	</div>
	<div id="documentation" style="width: 700px;"><br>
		Files with coordinates must have the ".txt" extension
	</div>
<script>
	'use strict'

	window.onload = function() {

		var points = [];
		var x1,x2,x3,y1,y2,y3;
		var tooltipShows = true;
		var documentationIsOpen = false;
		var isTriangulate = false;
		var canvas = document.querySelector('#canvas');
		var CContext = canvas.getContext('2d');
		var fileField = document.querySelector("#fileField");
		var calculateFigureAreaButton = document.querySelector('#calculateFigureAreaButton');
		var clearContextButton = document.querySelector('#clearContextButton');
		var documentationButton = document.querySelector('#documentationButton');
		var documentationBlock = document.querySelector('#documentation');
		var splitFigureButton = document.querySelector('#splitFigureButton');	
		var uploadFileButton = document.querySelector('#uploadFileButton');
		var originX = -8;
		var originY = -125;
		clearContextButton.disabled = true;
		splitFigureButton.disabled = true;
		calculateFigureAreaButton.disabled = true;

		CContext.translate(originX, originY);

		var click=0;

		canvas.onclick = function(eventCoordinate) {
			click++;
			eventCoordinate = (window.event) ? window.event : eventCoordinate;
			var pointWeight = 2;
			CContext.strokeStyle = 'red';
			CContext.fillStyle = '#f39c12';
			CContext.lineWidth = 2;
			CContext.stroke();
			CContext.fillRect(eventCoordinate.clientX, eventCoordinate.clientY, pointWeight, pointWeight);	
			CContext.stroke();	
			points[points.length] = [eventCoordinate.clientX, eventCoordinate.clientY];
			if(points.length > 0) {
				fileField.disabled = true;
				uploadFileButton.disabled = true;
				clearContextButton.disabled = false;
			}
			if(points.length > 2) splitFigureButton.disabled = false;
			console.log(click);
		};

		clearContextButton.onclick = function() {
			clearContext();
		};

		documentationButton.onclick = function() {
			if(documentationIsOpen === false) {
				documentationBlock.style.display = 'block';
				documentationIsOpen = true;
				documentationButton.innerText = 'Close documentation';
			} else {
				documentationBlock.style.display = 'none';
				documentationIsOpen = false;
				documentationButton.innerText = 'Open documentation';
			} 
		};

		$("form[name='uploadFileForm']").submit(function(e) { 
                var formData = new FormData($(this)[0]);
                $.ajax({
                    url: 'file.php',
                    type: "POST",
                    data: formData,
                    async: false,
                    success : function (polygon) {
			            clearContextButton.disabled = false;
						splitFigureButton.disabled = false;
						var polygon = $.parseJSON(polygon);
						for(var i=0; i<polygon.length; i++) {
							var coord = polygon[i].split(" ");
							points[points.length] = [parseInt(coord[0])-originX, parseInt(coord[1])-originY];
						    if( (parseInt(coord[0]) > canvas.width) ) 
						    	resizeContext(canvas, CContext, parseInt(coord[0])-originX+30, canvas.height+0, originX, originY);
						    if( (parseInt(coord[1]) > canvas.height) ) 
						    	resizeContext(canvas, CContext, canvas.width+0, parseInt(coord[1])-originY+30, originX, originY);
							var pointWeight = 2;
							CContext.strokeStyle = 'red';
							CContext.fillStyle = '#f39c12';
							CContext.lineWidth = 2;
							CContext.stroke();
							CContext.fillRect(parseInt(coord[0])-originX, parseInt(coord[1])-originY, pointWeight, pointWeight);	
							CContext.stroke();	
						}
		        	},
                    error: function(msg) {
                        alert('Ошибка!');
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
                e.preventDefault();
                fileField.disabled = true;
				uploadFileButton.disabled = true;
            });

		splitFigureButton.onclick = function() {
			if(isTriangulate == false) {
				triangulateFigure();
				isTriangulate = true;
				calculateFigureAreaButton.disabled = false;
				uploadFileButton.disabled = true;
				canvas.onclick = function() { return false };
			}
		};

		calculateFigureAreaButton.onclick = function() {
			alert(calculateFigureArea(x1,y1, x2,y2, x3,y3));
		};

		function triangulateFigure() {

			console.time("triangulate");
        	var triangles = Delaunay.triangulate(points);
       		console.timeEnd("triangulate");
	        for(var i = triangles.length; i;) {

		        CContext.beginPath();
			    i--; CContext.moveTo(points[triangles[i]][0], points[triangles[i]][1]);

			    x1 = points[triangles[i]][0];
			    y1 =  points[triangles[i]][1];

			    i--; CContext.lineTo(points[triangles[i]][0], points[triangles[i]][1]);

			    x2 = points[triangles[i]][0];
			    y2 = points[triangles[i]][1];

			    i--; CContext.lineTo(points[triangles[i]][0], points[triangles[i]][1]);

			    x3 = points[triangles[i]][0];
			    y3 = points[triangles[i]][1];

			    CContext.closePath();
			    CContext.stroke();

	        }
	        isTriangulate = true;
		}

		
		function calculateFigureArea(x1,y1, x2,y2, x3,y3) {
			var s=0;
			s += ((x1-x3)*(y2-y3)-(x2-x3)*(y1-y3)); 
			return Math.abs(s/2);
		}

		function resizeContext(canvas, context, width, height, originX, originY) {
	        var tempCanvas = document.createElement('canvas');
	        tempCanvas.width = context.canvas.width;
	        tempCanvas.height = context.canvas.height;
	        var tempContext = tempCanvas.getContext("2d");
	    
	        tempContext.drawImage(context.canvas, 0, 0);
	        canvas.width = width;
	        canvas.height = height;
	        context.drawImage(tempContext.canvas, 0, 0);
	        context.translate(originX, originY);
    	}

    	function clearContext() {
    		location.reload();
    	}

	};
</script>
<br><canvas id="canvas" width="1200px" height="500px"></canvas>
</body>
</html>