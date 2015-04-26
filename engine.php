<html>
<head>
 <title> BetruEngine </title>
            <style type="text/css">
                canvas {border: 1px solid black;}
            </style>

    <!-- MATH Libraries //-->
<script type='text/javascript' src='matrix/gl-matrix-min.js'></script>

	<script id = "code-js" type="text/javascript">

	var indices = [];
	var vertices = [];

	var rotation = [180,0,0];
	var home     = [0,0,-50];
    var position = [0,0,-50];

    var fig_rotation = [1, 0, 0];
  //  var rot_direction = [1, 1, 1, 0];

	var indBuffer = null;
	var vertBuffer = null;

    var gl = null;
	var prg = null;

	var c_width = 0;
	var c_height = 0;

	var VSHADER_SOURCE = null;
	var FSHADER_SOURCE = null;

	var mvMatrix    = mat4.create();   // The Model-View matrix
    var pMatrix     = mat4.create();
    var cMatrix     = mat4.create();

    var oMatrix = mat4.create();

	function initTransforms()
{
   // mvMatrix = mat4.create();
   // pMatrix = mat4.create();
   // cMatrix = mat4.create();

    //Initialize Model-View matrix
    mat4.identity(mvMatrix);
    mat4.translate(mvMatrix, home);
   // displayMatrix(mvMatrix);

    //Initialize Camera matrix as the inverse of the Model-View Matrix
    mat4.identity(cMatrix);
    mat4.inverse(mvMatrix,cMatrix);

    //Initialize Perspective matrix
    mat4.identity(pMatrix);

    mat4.identity(oMatrix);
    oMatrix[3] = 0;
    //mat4.translate(rot_direction);
  //  mat4.translate(rot_direction);

}

function updateTransforms()
{


     mat4.perspective(60, c_width / c_height, 0.1, 1000.0, pMatrix);

    mat4.identity(cMatrix);
    mat4.translate(cMatrix,position);
    mat4.rotateX(cMatrix,rotation[0]*Math.PI/180);
    mat4.rotateY(cMatrix,rotation[1]*Math.PI/180);
    mat4.rotateZ(cMatrix,rotation[2]*Math.PI/180);

     mat4.rotateX(oMatrix,fig_rotation[0]*Math.PI/180);
    mat4.rotateY(oMatrix,fig_rotation[1]*Math.PI/180);
    mat4.rotateZ(oMatrix,fig_rotation[2]*Math.PI/180);


}

function setMatrixUniforms()
{
    mat4.inverse(cMatrix, mvMatrix);      //Obtain Model-View matrix from Camera Matrix
      //   displayMatrix(cMatrix);

    gl.uniformMatrix4fv(prg.uPMatrix, false, pMatrix);    //Maps the Perspective matrix to the uniform prg.uPMatrix
     gl.uniformMatrix4fv(prg.uMVMatrix, false, mvMatrix);
     gl.uniformMatrix4fv(prg.uOMatrix, false, oMatrix);
}

    function getGLContext()
        {
            var canvas = document.getElementById("canvas-element-id");
            if (canvas == null)
            {
                alert("there is no canvas on this page");
                return;
            }
             else
            {
                c_width = canvas.width;
                c_height = canvas.height;
            }
            var names = ["webgl", "experimental-webgl", "webkit-3d", "moz-webgl"];
            for (var i = 0; i < names.length; ++i)
            {
                {
                    gl = canvas.getContext(names[i]);
                }
                if (gl)
                break;
            }
            if (gl == null)
            {
                alert("WebGL is not available");
            }
        }


    function readShaderFile(fileName, shader_name)
    {
  var request = new XMLHttpRequest();
  var shader = null;

  request.onreadystatechange = function() {
    if (request.readyState === 4 && request.status !== 404) {
      shader = onReadShader(request.responseText, shader_name);
    }
  }
  request.open('GET', fileName, false); // Создаём запрос получения файла (синхронный!!!)
  request.send();

    return shader;
}

// Шейдер загружен из файла
function onReadShader(fileString, shader_name) {

      var shader = null;

  if (shader_name == 'v') { // Вершинный шейдер
    VSHADER_SOURCE = fileString;
    shader = gl.createShader(gl.VERTEX_SHADER);
     gl.shaderSource(shader, VSHADER_SOURCE);
  } else
  if (shader_name == 'f') { // Фрагментный шейдер
    FSHADER_SOURCE = fileString;
    shader = gl.createShader(gl.FRAGMENT_SHADER);
    gl.shaderSource(shader, FSHADER_SOURCE);
   // alert(FSHADER_SOURCE);
  }
  else
  {
  alert("Incorrect shader type");
  return null;
  }

        gl.compileShader(shader);

    if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
    alert(gl.getShaderInfoLog(shader));
    return null;
        }

   return shader;
}


	function initProgram()
{
       var fgShader = readShaderFile('./shader-fs.glsl', 'f');
		var vxShader = readShaderFile('./shader-vs.glsl', 'v');

		prg = gl.createProgram();
		gl.attachShader(prg, vxShader);
		gl.attachShader(prg, fgShader);
		gl.linkProgram(prg);

		if (!gl.getProgramParameter(prg, gl.LINK_STATUS)) {
			alert("Could not initialise shaders");
		}

		gl.useProgram(prg);

		//The following lines allow us obtaining a reference to the uniforms and attributes defined in the shaders.

		prg.vertexPosition = gl.getAttribLocation(prg, "aVertexPosition");

		prg.uPMatrix         = gl.getUniformLocation(prg, "uPMatrix");
        prg.uMVMatrix        = gl.getUniformLocation(prg, "uMVMatrix");
        prg.uOMatrix         = gl.getUniformLocation(prg, "uOMatrix");

}

	function initBuffers()
{
	vertices = [-5, 5, 0.0,
			-5, -5, 0.0,
			5, -5, 0.0,
			5, 5, 0.0];

	indices = [3,2,1,3,1,0];

	vertBuffer = gl.createBuffer();
		gl.bindBuffer(gl.ARRAY_BUFFER, vertBuffer);
		gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(vertices), gl.STATIC_DRAW);
		gl.bindBuffer(gl.ARRAY_BUFFER, null);

		//The following code snippet creates a vertex buffer and binds the indices to it
		indBuffer = gl.createBuffer();
		gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, indBuffer);
		gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, new Uint16Array(indices), gl.STATIC_DRAW);
		gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, null);
}

    function requestAnimFrame(callback)
    {
        window.setTimeout(callback);
    }

    //*/function requestAnimFrame () {
   // return window.requestAnimationFrame ||
    //     window.webkitRequestAnimationFrame ||
    //     window.mozRequestAnimationFrame ||
     //    window.oRequestAnimationFrame ||
     //    window.msRequestAnimationFrame ||
    //     function(/* function FrameRequestCallback */ callback, /* DOMElement Element */ element) {
    //       window.setTimeout(callback, 1000/60);
     //    };
     //    }/*

     function configure()
     {
        	gl.clearColor(0.3, 0.3, 0.3, 1.0);
	gl.clearDepth(200.0);
    gl.enable(gl.DEPTH_TEST);
      gl.depthFunc(gl.LEQUAL);
      initTransforms();
     //  updateTransforms();
     //   setMatrixUniforms();

     }

	function drawScene()
{

 gl.viewport(0,0,c_width, c_height);
    gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);


       updateTransforms();
        setMatrixUniforms();

          gl.enableVertexAttribArray(prg.vertexPosition);

    gl.bindBuffer(gl.ARRAY_BUFFER, vertBuffer);
    gl.vertexAttribPointer(prg.aVertexPosition, 3, gl.FLOAT, false, 0, 0);


    gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, indBuffer);
    gl.drawElements(gl.TRIANGLES, indices.length, gl.UNSIGNED_SHORT,0);

    gl.bindBuffer(gl.ARRAY_BUFFER, null);
            gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, null);
}


	function renderLoop()
{
   // fig_rotation[0]++;
    requestAnimFrame(renderLoop);
	drawScene();
}

	function runWebGLApp()
{
	getGLContext();
	initProgram();
	initBuffers();

	configure();

    renderLoop();

}

	</script>

        </head>
        <body onLoad = 'runWebGLApp()'>
            <canvas id="canvas-element-id" width="480" height="400">
                Your browser does not support HTML5
            </canvas>
        </body>

</html>
