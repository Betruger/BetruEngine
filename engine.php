<html>

<head>
<title>BetruEngine v.0.0.2</title>

<script type="text/javascript" src="matrix/gl-matrix-min.js"></script>
<script type="text/javascript" src="utils.js"></script>

<script type="text/javascript">

    var objects = [];

    var texture = null;

    var rotation = [0, 0, 0];
    var position = [0, 0, -5];

    var obj_rot = [0, 0, 0];

    var lightposition = [0, 0, -5];
    var lightambient = [0.40,0.40,0.40,1.0];
    var lightdiffuse = [1.0, 1.0, 1.0, 1.0];

    var materialdiffuse = [1.0, 1.0, 1.0, 1.0];

    var mvMatrix = mat4.create();
    var pMatrix = mat4.create();
    var nMatrix = mat4.create();
    var cMatrix = mat4.create();
    var tempMatrix = mat4.create();

    var shaderProgram;

    var cubeVertexPositionBuffer;
    var cubeVertexTextureCoordBuffer;
    var cubeVertexIndexBuffer;
    var cubeNormalBuffer;

    function initShaders() {
    var fragmentShader = readShaderFile("shader-fs.glsl", 'f');
    var vertexShader = readShaderFile("shader-vs.glsl", 'v');

    shaderProgram = gl.createProgram();
    gl.attachShader(shaderProgram, vertexShader);
    gl.attachShader(shaderProgram, fragmentShader);
    gl.linkProgram(shaderProgram);

    if (!gl.getProgramParameter(shaderProgram, gl.LINK_STATUS)) {
        alert("Could not initialise shaders");
    }

    gl.useProgram(shaderProgram);

    shaderProgram.vertexPositionAttribute = gl.getAttribLocation(shaderProgram, "aVertexPosition");
    gl.enableVertexAttribArray(shaderProgram.vertexPositionAttribute);

    shaderProgram.textureCoordAttribute = gl.getAttribLocation(shaderProgram, "aTextureCoord");
    gl.enableVertexAttribArray(shaderProgram.textureCoordAttribute);

    shaderProgram.vertexNormalAttribute = gl.getAttribLocation(shaderProgram, "aVertexNormal");
    gl.enableVertexAttribArray(shaderProgram.vertexNormalAttribute);

    shaderProgram.pMatrixUniform = gl.getUniformLocation(shaderProgram, "uPMatrix");
    shaderProgram.mvMatrixUniform = gl.getUniformLocation(shaderProgram, "uMVMatrix");
    shaderProgram.nMatrixUniform = gl.getUniformLocation(shaderProgram, "uNMatrix");
    shaderProgram.samplerUniform = gl.getUniformLocation(shaderProgram, "uSampler");

    shaderProgram.uLightAmbient     = gl.getUniformLocation(shaderProgram, "uLightAmbient");
    shaderProgram.uLightDiffuse     = gl.getUniformLocation(shaderProgram, "uLightDiffuse");
    shaderProgram.uLightPosition    = gl.getUniformLocation(shaderProgram, "uLightPosition");
    shaderProgram.uMaterialDiffuse    = gl.getUniformLocation(shaderProgram, "uMaterialDiffuse");

    gl.uniform3fv(shaderProgram.uLightPosition,    lightposition);
    gl.uniform4fv(shaderProgram.uLightAmbient,      lightambient);
    gl.uniform4fv(shaderProgram.uLightDiffuse,     lightdiffuse);
    gl.uniform4fv(shaderProgram.uMaterialDiffuse,     materialdiffuse);
    }

    function handleLoadedTexture(texture) {
    gl.bindTexture(gl.TEXTURE_2D, texture);
    gl.pixelStorei(gl.UNPACK_FLIP_Y_WEBGL, true);
    gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, texture.image);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.NEAREST);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.NEAREST);
    gl.bindTexture(gl.TEXTURE_2D, null);
    }

    function initTexture() {
        texture = gl.createTexture();
        texture.image = new Image();
        texture.image.onload = function () {
            handleLoadedTexture(texture)
        }
        texture.image.src = "bricks.png";
    }

    function setMatrixUniforms() {

    gl.uniformMatrix4fv(shaderProgram.pMatrixUniform, false, pMatrix);
    gl.uniformMatrix4fv(shaderProgram.mvMatrixUniform, false, mvMatrix);

    mat4.identity(nMatrix);
    mat4.set(mvMatrix, nMatrix);
    mat4.inverse(nMatrix);
    mat4.transpose(nMatrix);

    gl.uniformMatrix4fv(shaderProgram.nMatrixUniform, false, nMatrix);
    }

    var obj;

    function loadObjectBuffers(o)
    {
        obj = o;
        //var o = loadObject("cube.json");

        cubeVertexPositionBuffer = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, cubeVertexPositionBuffer);

        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(o.vertices), gl.STATIC_DRAW);
        cubeVertexPositionBuffer.itemSize = 3;
        cubeVertexPositionBuffer.numItems = 24;

        cubeVertexTextureCoordBuffer = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, cubeVertexTextureCoordBuffer);

        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(o.texture_coords), gl.STATIC_DRAW);
        cubeVertexTextureCoordBuffer.itemSize = 2;
        cubeVertexTextureCoordBuffer.numItems = 24;

        cubeVertexIndexBuffer = gl.createBuffer();
        gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, cubeVertexIndexBuffer);

        cubeNormalBuffer = gl.createBuffer();
        gl.bindBuffer(gl.ARRAY_BUFFER, cubeNormalBuffer);
        gl.bufferData(gl.ARRAY_BUFFER, new Float32Array(calculateNormals(o.vertices, o.indices)), gl.STATIC_DRAW);

        gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, new Uint16Array(o.indices), gl.STATIC_DRAW);
        cubeVertexIndexBuffer.itemSize = 1;
        cubeVertexIndexBuffer.numItems = 36;
    }

    function loadObject(filename)
{
    var request = new XMLHttpRequest();
    request.open("GET",filename);

    request.onreadystatechange = function() {
            if (request.readyState == 4) {
                if(request.status == 404) {
                    console.info(filename + ' does not exist');
                }
                else {
                var o = JSON.parse(request.responseText);
                    //o.alias = (alias==null)?'none':alias;
                    //o.remote = true;
                   // return o;
                loadObjectBuffers(o);
                return;
                }
            }
        }
        request.send();
}


    function drawScene() {
        gl.viewport(0, 0, c_width, c_height);
        gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);

        mat4.perspective(45, c_width / c_height, 0.1, 100.0, pMatrix);

        mat4.identity(mvMatrix);

        mat4.rotate(mvMatrix, degToRad(rotation[0]), [1, 0, 0]);
        mat4.rotate(mvMatrix, degToRad(rotation[1]), [0, 1, 0]);
        mat4.rotate(mvMatrix, degToRad(rotation[2]), [0, 0, 1]);

        mat4.translate(mvMatrix, position);

        mat4.identity(tempMatrix);
        mat4.set(mvMatrix, tempMatrix);

        if (obj)
        {
            for (var i = 0; i < objects.length; i++)
            {
        var objectd = objects[i];

        mat4.identity(cMatrix);
        mat4.translate(cMatrix, objectd.obj_pos);

        mat4.rotate(cMatrix, degToRad(obj_rot[0]), [1, 0, 0]);
        mat4.rotate(cMatrix, degToRad(obj_rot[1]), [0, 1, 0]);
        mat4.rotate(cMatrix, degToRad(obj_rot[2]), [0, 0, 1]);

        mat4.inverse(cMatrix);

        var lightpos = [1.0, 1.0, 1.0];
        lightpos[0] = -lightposition[0];
        lightpos[1] = -lightposition[1];
        lightpos[2] = -lightposition[2];

         mat4.multiplyVec3(cMatrix, lightpos, lightpos);

        gl.uniform3fv(shaderProgram.uLightPosition,  lightpos);

        mat4.translate(mvMatrix, objectd.obj_pos);

         mat4.rotate(mvMatrix, degToRad(obj_rot[0]), [1, 0, 0]);
        mat4.rotate(mvMatrix, degToRad(obj_rot[1]), [0, 1, 0]);
        mat4.rotate(mvMatrix, degToRad(obj_rot[2]), [0, 0, 1]);

        gl.bindBuffer(gl.ARRAY_BUFFER, cubeVertexPositionBuffer);
        gl.vertexAttribPointer(shaderProgram.vertexPositionAttribute, cubeVertexPositionBuffer.itemSize, gl.FLOAT, false, 0, 0);

        gl.bindBuffer(gl.ARRAY_BUFFER, cubeNormalBuffer);
        gl.vertexAttribPointer(shaderProgram.vertexNormalAttribute, 3, gl.FLOAT, false, 0, 0);

        gl.bindBuffer(gl.ARRAY_BUFFER, cubeVertexTextureCoordBuffer);
        gl.vertexAttribPointer(shaderProgram.textureCoordAttribute, cubeVertexTextureCoordBuffer.itemSize, gl.FLOAT, false, 0, 0);

        gl.activeTexture(gl.TEXTURE0);
        gl.bindTexture(gl.TEXTURE_2D, texture);
        gl.uniform1i(shaderProgram.samplerUniform, 0);

        gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, cubeVertexIndexBuffer);
        setMatrixUniforms();

        gl.drawElements(gl.TRIANGLES, cubeVertexIndexBuffer.numItems, gl.UNSIGNED_SHORT, 0);
        gl.bindBuffer(gl.ARRAY_BUFFER, null);
        gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, null);

        mat4.set(tempMatrix, mvMatrix);
}
   obj_rot[0]++;
   obj_rot[1]++;
   }
   }


    function tick() {
        requestAnimFrame(tick);
        drawScene();
    }



function mouseLoop(e)
{
 var movementX = e.movementX ||
      e.mozMovementX          ||
      e.webkitMovementX       ||
      0;

  var movementY = e.movementY ||
      e.mozMovementY      ||
      e.webkitMovementY   ||
      0;

  rotation[1] += movementX;

  rotation[0] += movementY;

}


var currentlyPressedKeys = {};

  function handleKeyDown(event) {
    currentlyPressedKeys[event.keyCode] = true;

    if (String.fromCharCode(event.keyCode) == "W") {
      position[0] -= Math.sin(degToRad( rotation[1] )  ) * Math.cos(degToRad(rotation[0]));
    position[2] += Math.cos(degToRad( rotation[1] )  ) * Math.cos(degToRad(rotation[0]));
    position[1] += Math.sin(degToRad(rotation[0]));
    }

      if (String.fromCharCode(event.keyCode) == "S") {
      position[0] += Math.sin(degToRad( rotation[1] )  ) * Math.cos(degToRad(rotation[0]));
    position[2] -= Math.cos(degToRad( rotation[1] )  ) * Math.cos(degToRad(rotation[0]));
    position[1] -= Math.sin(degToRad(rotation[0]));
    }

    if (String.fromCharCode(event.keyCode) == "A")
    {
     position[0] += Math.cos(degToRad(rotation[1]));
     position[2] += Math.sin(degToRad(rotation[1]));
    }

    if (String.fromCharCode(event.keyCode) == "D")
    {
     position[0] -= Math.cos(degToRad(rotation[1]));
     position[2] -= Math.sin(degToRad(rotation[1]));
    }

  }

  function handleKeyUp(event) {
    currentlyPressedKeys[event.keyCode] = false;
  }

function Object (position)
{
    this.obj_pos = position;
}

    function addObjects()
    {
        var object1 = new Object( [0.0, 0.0, 10.0] );

        objects.push(object1);

        var object2 = new Object( [0.0, 0.0, 0.0] );

        objects.push(object2);
    }

    function webGLStart() {
        initGL();
        initShaders();

        loadObject("cube.json");

        initTexture();

        addObjects();

        setPointerLock();

        gl.clearColor(0.0, 0.0, 0.0, 1.0);
        gl.enable(gl.DEPTH_TEST);
         gl.depthFunc(gl.LESS);

         document.onkeydown = handleKeyDown;
        document.onkeyup = handleKeyUp;

        tick();
    }


</script>

</head>

<body onload="webGLStart();">

    <canvas id="canvas_id" style="border: none;" width="800" height="600"></canvas>

</body>

</html>
