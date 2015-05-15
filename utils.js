var canvas;
var gl;

var EXT;

function initGL() {
        canvas = document.getElementById("your_canvas");
            if (canvas == null)
            {
                alert("there is no canvas on this page");
                return;
            }
        else
            {
            canvas.width=window.innerWidth;
            canvas.height=window.innerHeight;
           // c_width = canvas.width;
           // c_height = canvas.height;
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

            EXT = gl.getExtension("OES_element_index_uint") ||
      gl.getExtension("MOZ_OES_element_index_uint") ||
        gl.getExtension("WEBKIT_OES_element_index_uint");
    }


function onReadShader(fileString, shader_name) {

      var shader = null;

  if (shader_name == 'v') { // ��������� ������
    VSHADER_SOURCE = fileString;
    shader = gl.createShader(gl.VERTEX_SHADER);
     gl.shaderSource(shader, VSHADER_SOURCE);
    // alert (VSHADER_SOURCE);
  } else
  if (shader_name == 'f') { // ����������� ������
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

function readShaderFile(fileName, shader_name)
    {
  var request = new XMLHttpRequest();
  var shader = null;

  request.onreadystatechange = function() {
    if (request.readyState === 4 && request.status !== 404) {
   // return request.responseText;
      shader = onReadShader(request.responseText, shader_name);
    }
  }
  request.open('GET', fileName, false); // ������ ������ ��������� ����� (����������!!!)
  request.send();

    return shader;
}



window.requestAnimFrame = (function() {
  return window.requestAnimationFrame ||
         window.webkitRequestAnimationFrame ||
         window.mozRequestAnimationFrame ||
         window.oRequestAnimationFrame ||
         window.msRequestAnimationFrame ||
         function(/* function FrameRequestCallback */ callback, /* DOMElement Element */ element) {
           window.setTimeout(callback, 1000/60);
         };
})();

function lockChangeAlert() {
  if(document.pointerLockElement === canvas ||
  document.mozPointerLockElement === canvas ||
  document.webkitPointerLockElement === canvas) {
   // console.log('The pointer lock status is now locked');
   document.onmousemove = mouseLoop;
    //document.addEventListener("mousemove", mouseLoop, false);
  } else {
   // console.log('The pointer lock status is now unlocked');
   document.onmousemove = null;
   // document.removeEventListener("mousemove", mouseLoop, false);
  }
}

function setPointerLock ()
{
    canvas.requestPointerLock = canvas.requestPointerLock ||
           canvas.mozRequestPointerLock ||
           canvas.webkitRequestPointerLock;

    document.exitPointerLock = document.exitPointerLock ||
         document.mozExitPointerLock ||
         document.webkitExitPointerLock;

         canvas.onclick = function() {
  canvas.requestPointerLock();
}

document.onpointerlockchange = lockChangeAlert;
document.onmozpointerlockchange = lockChangeAlert;
document.onwebkitpointerlockchange = lockChangeAlert;
}

function calculateNormals (vs, ind)
{
        var x=0;
        var y=1;
        var z=2;

        var ns = [];
        for(var i=0;i<vs.length;i=i+3){ //for each vertex, initialize normal x, normal y, normal z
            ns[i+x]=0.0;
            ns[i+y]=0.0;
            ns[i+z]=0.0;
        }

        for(var i=0;i<ind.length;i=i+3){ //we work on triads of vertices to calculate normals so i = i+3 (i = indices index)
            var v1 = [];
            var v2 = [];
            var normal = [];
            //p2 - p1
            v1[x] = vs[3*ind[i+2]+x] - vs[3*ind[i+1]+x];
            v1[y] = vs[3*ind[i+2]+y] - vs[3*ind[i+1]+y];
            v1[z] = vs[3*ind[i+2]+z] - vs[3*ind[i+1]+z];
            //p0 - p1
            v2[x] = vs[3*ind[i]+x] - vs[3*ind[i+1]+x];
            v2[y] = vs[3*ind[i]+y] - vs[3*ind[i+1]+y];
            v2[z] = vs[3*ind[i]+z] - vs[3*ind[i+1]+z];
            //cross product by Sarrus Rule
            normal[x] = v1[y]*v2[z] - v1[z]*v2[y];
            normal[y] = v1[z]*v2[x] - v1[x]*v2[z];
            normal[z] = v1[x]*v2[y] - v1[y]*v2[x];
            for(j=0;j<3;j++){ //update the normals of that triangle: sum of vectors
                ns[3*ind[i+j]+x] =  ns[3*ind[i+j]+x] + normal[x];
                ns[3*ind[i+j]+y] =  ns[3*ind[i+j]+y] + normal[y];
                ns[3*ind[i+j]+z] =  ns[3*ind[i+j]+z] + normal[z];
            }
        }
        //normalize the result
        for(var i=0;i<vs.length;i=i+3){ //the increment here is because each vertex occurs with an offset of 3 in the array (due to x, y, z contiguous values)

            var nn=[];
            nn[x] = ns[i+x];
            nn[y] = ns[i+y];
            nn[z] = ns[i+z];

            var len = Math.sqrt((nn[x]*nn[x])+(nn[y]*nn[y])+(nn[z]*nn[z]));
            if (len == 0) len = 1.0;

            nn[x] = nn[x]/len;
            nn[y] = nn[y]/len;
            nn[z] = nn[z]/len;

            ns[i+x] = nn[x];
            ns[i+y] = nn[y];
            ns[i+z] = nn[z];
        }

        return ns;
    }
